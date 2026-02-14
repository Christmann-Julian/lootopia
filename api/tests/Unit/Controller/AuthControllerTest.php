<?php

namespace App\Tests\Unit\Controller;

use App\Controller\AuthController;
use App\Dto\User\RegisterUserRequest;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\RefreshTokenManager;
use App\Validator\DtoValidator;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthControllerTest extends TestCase
{
    private AuthController $controller;
    private UserRepository&MockObject $userRepository;
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private JWTTokenManagerInterface&MockObject $jwtManager;
    private RefreshTokenManager&MockObject $refreshTokenManager;
    private TranslatorInterface&MockObject $translator;
    private EntityManagerInterface&MockObject $entityManager;
    private DtoValidator&MockObject $dtoValidator;
    private EmailVerifier&MockObject $emailVerifier;
    private RefreshTokenRepository&MockObject $refreshTokenRepository;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $this->refreshTokenManager = $this->createMock(RefreshTokenManager::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dtoValidator = $this->createMock(DtoValidator::class);
        $this->emailVerifier = $this->createMock(EmailVerifier::class);
        $this->refreshTokenRepository = $this->createMock(RefreshTokenRepository::class);

        $this->controller = new AuthController('http://localhost:3000');
    }

    public function testLoginSuccess(): void
    {
        $email = 'test@example.com';
        $password = 'password123';

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'email' => $email,
            'password' => $password,
            'client_type' => 'web',
        ]));

        $user = new User();
        $user->setEmail($email);
        $user->setIsVerified(true);

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn($user);

        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $password)
            ->willReturn(true);

        $this->jwtManager->expects($this->once())
            ->method('create')
            ->with($user)
            ->willReturn('fake_jwt_token');

        $expectedResponse = new JsonResponse(['token' => 'fake_jwt_token']);
        $this->refreshTokenManager->expects($this->once())
            ->method('createResponseWithJwt')
            ->willReturn($expectedResponse);

        $response = $this->controller->login(
            $request,
            $this->userRepository,
            $this->passwordHasher,
            $this->jwtManager,
            $this->refreshTokenManager,
            $this->translator
        );

        $this->assertSame($expectedResponse, $response);
    }

    public function testLoginInvalidCredentials(): void
    {
        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'email' => 'test@example.com',
            'password' => 'wrong_password',
        ]));

        $user = new User();

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->passwordHasher->method('isPasswordValid')->willReturn(false);

        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Invalid credentials');

        try {
            $this->controller->login(
                $request,
                $this->userRepository,
                $this->passwordHasher,
                $this->jwtManager,
                $this->refreshTokenManager,
                $this->translator
            );
            $this->fail('ApiException expected');
        } catch (ApiException $e) {
            $this->assertEquals(Response::HTTP_UNAUTHORIZED, $e->getStatusCode());
        }
    }

    public function testLoginUnverifiedUser(): void
    {
        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'email' => 'unverified@example.com',
            'password' => 'password',
        ]));

        $user = new User();
        $user->setIsVerified(false);

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->passwordHasher->method('isPasswordValid')->willReturn(true);

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('auth.email_not_verified')
            ->willReturn('Email not verified');

        try {
            $this->controller->login(
                $request,
                $this->userRepository,
                $this->passwordHasher,
                $this->jwtManager,
                $this->refreshTokenManager,
                $this->translator
            );
            $this->fail('ApiException expected');
        } catch (ApiException $e) {
            $this->assertEquals(Response::HTTP_FORBIDDEN, $e->getStatusCode());
        }
    }

    public function testRegisterSuccess(): void
    {
        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@doe.com',
            'password' => 'StrongPass1!',
            'company' => 'Corp',
        ]));

        $this->dtoValidator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(RegisterUserRequest::class));

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn('hashed_password');

        $this->entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class));
        $this->entityManager->expects($this->once())->method('flush');

        $this->emailVerifier->expects($this->once())
            ->method('sendEmailConfirmation')
            ->with('app_auth_verify_email', $this->isInstanceOf(User::class));

        $response = $this->controller->register(
            $request,
            $this->passwordHasher,
            $this->entityManager,
            $this->dtoValidator,
            $this->emailVerifier
        );

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testRefreshSuccess(): void
    {
        $refreshTokenString = 'some_refresh_token';
        $clientIp = '127.0.0.1';

        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => $clientIp], (string) json_encode([
            'refresh_token' => $refreshTokenString,
            'client_type' => 'mobile',
        ]));

        $refreshTokenEntity = new RefreshToken();
        $refreshTokenEntity->setToken($refreshTokenString);
        $refreshTokenEntity->setUserIdentifier('user@example.com');
        $refreshTokenEntity->setIpAddress($clientIp);

        $user = new User();
        $user->setEmail('user@example.com');

        $this->refreshTokenRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => $refreshTokenString])
            ->willReturn($refreshTokenEntity);

        $this->refreshTokenManager->expects($this->once())
            ->method('isRefreshTokenValid')
            ->with($refreshTokenEntity)
            ->willReturn(true);

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'user@example.com'])
            ->willReturn($user);

        $this->jwtManager->expects($this->once())
            ->method('create')
            ->with($user)
            ->willReturn('new_jwt_token');

        $this->refreshTokenManager->expects($this->once())
            ->method('revokeRefreshToken')
            ->with($refreshTokenEntity);

        $expectedResponse = new JsonResponse(['token' => 'new_jwt_token']);
        $this->refreshTokenManager->expects($this->once())
            ->method('createResponseWithJwt')
            ->willReturn($expectedResponse);

        $response = $this->controller->refresh(
            $request,
            $this->jwtManager,
            $this->refreshTokenManager,
            $this->refreshTokenRepository,
            $this->userRepository
        );

        $this->assertSame($expectedResponse, $response);
    }

    public function testRefreshIpMismatch(): void
    {
        $refreshTokenString = 'token_abc';

        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '192.168.1.50'], (string) json_encode([
            'refresh_token' => $refreshTokenString,
            'client_type' => 'mobile',
        ]));

        $refreshTokenEntity = new RefreshToken();
        $refreshTokenEntity->setToken($refreshTokenString);
        $refreshTokenEntity->setUserIdentifier('user@example.com');
        $refreshTokenEntity->setIpAddress('10.0.0.1');

        $user = new User();
        $user->setEmail('user@example.com');

        $this->refreshTokenRepository->method('findOneBy')->willReturn($refreshTokenEntity);
        $this->refreshTokenManager->method('isRefreshTokenValid')->willReturn(true);
        $this->userRepository->method('findOneBy')->willReturn($user);

        try {
            $this->controller->refresh(
                $request,
                $this->jwtManager,
                $this->refreshTokenManager,
                $this->refreshTokenRepository,
                $this->userRepository
            );
            $this->fail('ApiException expected');
        } catch (ApiException $e) {
            $this->assertEquals(Response::HTTP_FORBIDDEN, $e->getStatusCode());
            $this->assertEquals('IP address mismatch', $e->getMessage());
        }
    }
}
