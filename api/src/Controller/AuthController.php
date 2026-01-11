<?php

namespace App\Controller;

use App\Dto\User\RegisterUserRequest;
use App\Dto\User\ResetUserPasswordRequest;
use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\RefreshTokenManager;
use App\Validator\DtoValidator;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[OA\Parameter(
    name: 'locale',
    in: 'query',
    required: false,
    description: 'optional locale (ex: fr, en)',
    schema: new OA\Schema(type: 'string', example: 'fr')
)]
#[OA\Tag(name: 'auth', description: 'Authentication endpoints')]
#[Route('/api/auth', name: 'app_auth_')]
final class AuthController extends AbstractController
{
    #[OA\Post(
        summary: 'User login',
        description: 'Allows a user to log in and receive a JWT + Refresh Token.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'client_type', type: 'string', example: 'web'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1Q...'),
                        new OA\Property(property: 'refresh_token', type: 'string', example: 'c1b0f...'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Invalid credentials'),
            new OA\Response(response: 403, description: 'Email not verified'),
        ]
    )]
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenManager $refreshTokenManager,
        TranslatorInterface $translator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $clientType = $data['client_type'] ?? 'web';

        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            throw new ApiException(Response::HTTP_UNAUTHORIZED, $translator->trans('auth.invalid_credentials', [], 'messages'));
        }

        if (!$user->isVerified()) {
            throw new ApiException(Response::HTTP_FORBIDDEN, $translator->trans('auth.email_not_verified', [], 'messages'));
        }

        $jwt = $jwtManager->create($user);

        return $refreshTokenManager->createResponseWithJwt(
            $jwt,
            $user,
            $request,
            $clientType
        );
    }

    #[OA\Post(
        summary: 'Refresh JWT using refresh token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'refresh_token', type: 'string', example: 'c1b0f...'),
                    new OA\Property(property: 'client_type', type: 'string', example: 'web'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'New JWT issued',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string'),
                        new OA\Property(property: 'refresh_token', type: 'string'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Token expired or invalid'),
            new OA\Response(response: 403, description: 'IP address mismatch'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    #[Route('/token/refresh', name: 'token_refresh', methods: ['POST'])]
    public function refresh(
        Request $request,
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenManager $refreshTokenManager,
        RefreshTokenRepository $refreshTokenRepository,
        UserRepository $userRepository,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $refreshTokenString = $data['refresh_token'] ?? '';
        $clientType = $data['client_type'] ?? 'web';

        if ('web' == $clientType && empty($refreshTokenString)) {
            $refreshTokenString = $request->cookies->get('REFRESH_TOKEN', '');
        }

        $refreshToken = $refreshTokenRepository->findOneBy(['token' => $refreshTokenString]);

        if (!$refreshToken || !$refreshTokenManager->isRefreshTokenValid($refreshToken)) {
            throw new ApiException(Response::HTTP_UNAUTHORIZED, 'Token expired or invalid');
        }

        $user = $userRepository->findOneBy(['email' => $refreshToken->getUserIdentifier()]);
        if (!$user) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'User not found');
        }

        if ($refreshToken->getIpAddress() !== $request->getClientIp()) {
            throw new ApiException(Response::HTTP_FORBIDDEN, 'IP address mismatch');
        }

        $newJwt = $jwtManager->create($user);
        $refreshTokenManager->revokeRefreshToken($refreshToken);

        return $refreshTokenManager->createResponseWithJwt(
            $newJwt,
            $user,
            $request,
            $clientType
        );
    }

    #[OA\Post(
        summary: 'Logout and revoke refresh tokens',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'refresh_token', type: 'string', example: 'c1b0f...'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 204, description: 'Logged out, tokens revoked'),
        ]
    )]
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(
        Request $request,
        RefreshTokenRepository $refreshTokenRepository,
        RefreshTokenManager $refreshTokenManager,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $refreshTokenString = $data['refresh_token'] ?? '';

        if (empty($refreshTokenString)) {
            $refreshTokenString = $request->cookies->get('REFRESH_TOKEN', '');
        }

        $refreshToken = $refreshTokenRepository->findOneBy(['token' => $refreshTokenString]);

        if ($refreshToken) {
            $refreshTokenManager->revokeAllRefreshTokens($refreshToken);
        }

        $response = new JsonResponse(null, Response::HTTP_NO_CONTENT);
        $response->headers->clearCookie('REFRESH_TOKEN');

        return $response;
    }

    #[OA\Get(
        summary: 'Get current authenticated user info',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current user info',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'firstname', type: 'string', example: 'Jean'),
                        new OA\Property(property: 'lastname', type: 'string', example: 'Dupont'),
                        new OA\Property(property: 'email', type: 'string', example: 'jean.dupont@example.com'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Access denied'),
        ]
    )]
    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            throw new ApiException(Response::HTTP_UNAUTHORIZED, 'Access denied');
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'company' => $user->getCompany(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[OA\Post(
        summary: 'User registration',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'firstname', type: 'string', example: 'Jean'),
                    new OA\Property(property: 'lastname', type: 'string', example: 'Dupont'),
                    new OA\Property(property: 'company', type: 'string', example: 'ACME Corp', nullable: true),
                    new OA\Property(property: 'email', type: 'string', example: 'jean.dupont@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'Secret123!'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'user created successfully'),
            new OA\Response(response: 400, description: 'Validation error'),
        ]
    )]
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        DtoValidator $dtoValidator,
        EmailVerifier $emailVerifier,
        TranslatorInterface $translator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $registerUserRequest = new RegisterUserRequest(
            $data['firstname'] ?? '',
            $data['lastname'] ?? '',
            $data['company'] ?? null,
            $data['email'] ?? '',
            $data['password'] ?? ''
        );

        $dtoValidator->validate($registerUserRequest);

        $user = new User();
        $user->setFirstname($registerUserRequest->getFirstname())
            ->setLastname($registerUserRequest->getLastname())
            ->setEmail($registerUserRequest->getEmail())
            ->setCompany($registerUserRequest->getCompany())
            ->setIsVerified(false)
            ->setPassword($passwordHasher->hashPassword($user, $registerUserRequest->getPassword()))
            ->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@example.com'))
            ->to(new Address((string) $user->getEmail()))
            ->subject($translator->trans('email.confirm.subject', [], 'emails'))
            ->htmlTemplate('emails/confirm_email.html.twig')
            ->locale($request->getLocale())
            ->context(['locale' => $request->getLocale()]);

        $emailVerifier->sendEmailConfirmation('app_auth_verify_email', $user, $email);

        return new JsonResponse([
            'email' => $user->getEmail(),
        ], Response::HTTP_CREATED);
    }

    #[OA\Post(
        summary: 'Request email verification (resend)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 202, description: 'Email sent if the account exists'),
            new OA\Response(response: 400, description: 'Bad request (address not registered or already verified)'),
        ]
    )]
    #[Route('/verify/request', name: 'email_confirmation_request', methods: ['POST'])]
    public function sendEmailConfirmation(
        EmailVerifier $emailVerifier,
        Request $request,
        UserRepository $userRepository,
        TranslatorInterface $translator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = $userRepository->findOneBy(['email' => $data['email']]);

        if (!$user || null === $user->getEmail()) {
            throw new ApiException(Response::HTTP_BAD_REQUEST, $translator->trans('auth.email_address_not_registered', [], 'messages'));
        }

        if ($user->isVerified()) {
            throw new ApiException(Response::HTTP_BAD_REQUEST, $translator->trans('auth.email_address_already_verified', [], 'messages'));
        }

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@example.com'))
            ->to(new Address($user->getEmail()))
            ->subject($translator->trans('email.confirm.subject', [], 'emails'))
            ->htmlTemplate('emails/confirm_email.html.twig')
            ->locale($request->getLocale())
            ->context(['locale' => $request->getLocale()]);

        $emailVerifier->sendEmailConfirmation('app_auth_verify_email', $user, $email);

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }

    #[OA\Get(
        summary: 'Verify user email (via confirmation link)',
        parameters: [
            new OA\Parameter(name: 'email', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirect to frontend with success or error query param'),
        ]
    )]
    #[Route('/verify', name: 'verify_email', methods: ['GET'])]
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository,
        EmailVerifier $emailVerifier,
        TranslatorInterface $translator,
    ): Response {
        $email = $request->query->get('email');
        $message = '';

        try {
            if (!$email) {
                throw new \Exception('Email parameter missing');
            }

            $user = $userRepository->findOneBy(['email' => $email]);

            if (null === $user || null === $user->getEmail()) {
                throw new \Exception('User not found');
            }

            $emailVerifier->handleEmailConfirmation($request, $user);
            $message = '?success='.urlencode($translator->trans('auth.email_verified', [], 'messages'));
        } catch (\Exception $e) {
            $message = '?error='.urlencode($translator->trans('auth.signed_url_invalid', [], 'messages'));
        }

        return $this->redirect('http://localhost:5173/'.$message);
    }

    #[OA\Post(
        summary: 'Password reset request',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 202, description: 'Email sent if the account exists'),
        ]
    )]
    #[Route('/password/reset/request', name: 'password_reset_request', methods: ['POST'])]
    public function requestPasswordReset(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        TranslatorInterface $translator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(null, Response::HTTP_ACCEPTED);
        }

        $tokenString = bin2hex(random_bytes(32));
        $expiresAt = new \DateTimeImmutable('+15 minutes');

        $resetToken = new PasswordResetToken();
        $resetToken->setUser($user)
            ->setToken($tokenString)
            ->setExpiresAt($expiresAt)
            ->setIpAddress($request->getClientIp())
            ->setUserAgent($request->headers->get('User-Agent'));

        $entityManager->persist($resetToken);
        $entityManager->flush();

        $resetUrl = sprintf('http://localhost:5173/%s/reset-password?token=%s&email=%s', $request->getLocale(), $tokenString, urlencode((string) $user->getEmail()));

        $emailMessage = (new TemplatedEmail())
            ->from(new Address('no-reply@example.com'))
            ->to(new Address((string) $user->getEmail()))
            ->subject($translator->trans('email.reset.subject', [], 'emails'))
            ->htmlTemplate('emails/reset_password.html.twig')
            ->locale($request->getLocale())
            ->context(['resetUrl' => $resetUrl, 'expiresAt' => $expiresAt, 'locale' => $request->getLocale()]);

        $mailer->send($emailMessage);

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }

    #[OA\Post(
        summary: 'Reset user password using reset token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'token', type: 'string', example: 'abcdef123456...'),
                    new OA\Property(property: 'password', type: 'string', example: 'NewSecret123!'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 204, description: 'Password reset successful'),
            new OA\Response(response: 400, description: 'Invalid reset token or validation error'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    #[Route('/password/reset', name: 'password_reset', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        PasswordResetTokenRepository $passwordResetTokenRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        DtoValidator $dtoValidator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $dto = new ResetUserPasswordRequest(
            $data['token'] ?? '',
            $data['password'] ?? ''
        );

        $dtoValidator->validate($dto);

        $tokenString = $dto->getToken();
        $newPassword = $dto->getPassword();

        $token = $passwordResetTokenRepository->findOneBy(['token' => $tokenString]);

        if (!$token || $token->getExpiresAt() < new \DateTimeImmutable()) {
            throw new ApiException(Response::HTTP_BAD_REQUEST, $translator->trans('auth.invalid_reset_password_request', [], 'messages'));
        }

        $user = $token->getUser();
        if (!$user) {
            throw new ApiException(Response::HTTP_NOT_FOUND, $translator->trans('auth.user_not_found', [], 'messages'));
        }

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
