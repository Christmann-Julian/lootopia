<?php

namespace App\Security;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

/**
 * Class RefreshTokenManager.
 *
 * @author Julian CHRISTMANN
 *
 * Manages the creation, validation, and revocation of refresh tokens.
 */
class RefreshTokenManager
{
    public const REFRESH_TOKEN_TTL = '+7 days';
    public const REFRESH_TOKEN_COOKIE_NAME = 'REFRESH_TOKEN';
    public const WEB_CLIENT = 'web';
    public const MOBILE_CLIENT = 'mobile';

    /**
     * @param EntityManagerInterface $em the Doctrine entity manager
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RefreshTokenRepository $refreshTokenRepository,
    ) {
    }

    /**
     * Creates a new refresh token for the given user and request.
     *
     * @param User    $user    the user for whom the refresh token is created
     * @param Request $request the HTTP request containing client information
     *
     * @return RefreshToken the newly created refresh token
     */
    public function createRefreshToken(User $user, Request $request): RefreshToken
    {
        $refreshToken = new RefreshToken();
        $refreshToken
            ->setToken(Uuid::v4()->toString())
            ->setUserIdentifier($user->getUserIdentifier())
            ->setExpiresAt((new \DateTimeImmutable())->modify(self::REFRESH_TOKEN_TTL))
            ->setIpAddress($request->getClientIp())
            ->setUserAgent($request->headers->get('User-Agent'));

        $this->em->persist($refreshToken);
        $this->em->flush();

        return $refreshToken;
    }

    /**
     * Revokes the given refresh token.
     *
     * @param RefreshToken $refreshToken the refresh token to revoke
     */
    public function revokeRefreshToken(RefreshToken $refreshToken): void
    {
        $refreshToken->revoke();
        $this->em->flush();
    }

    /**
     * Revokes all refresh tokens associated with the user of the given refresh token.
     *
     * @param RefreshToken $refreshToken the refresh token whose user's tokens are to be revoked
     */
    public function revokeAllRefreshTokens(RefreshToken $refreshToken): void
    {
        $userIdentifier = $refreshToken->getUserIdentifier();
        $this->refreshTokenRepository->revokeAllForUser($userIdentifier);
    }

    /**
     * Checks if the given refresh token is valid.
     *
     * @param RefreshToken|null $refreshToken the refresh token to validate
     *
     * @return bool true if the refresh token is valid, false otherwise
     */
    public function isRefreshTokenValid(?RefreshToken $refreshToken): bool
    {
        return $refreshToken && !$refreshToken->isExpired() && !$refreshToken->isRevoked();
    }

    /**
     * Creates a JSON response containing the JWT and refresh token.
     *
     * @param string  $jwt        the JWT token
     * @param User    $user       the user for whom the tokens are created
     * @param Request $request    the HTTP request containing client information
     * @param string  $clientType The type of client (e.g., 'web' or 'mobile').
     *
     * @return JsonResponse the JSON response containing the tokens
     */
    public function createResponseWithJwt(
        string $jwt,
        User $user,
        Request $request,
        string $clientType = self::WEB_CLIENT,
    ): JsonResponse {
        $refreshToken = $this->createRefreshToken($user, $request);

        if (self::WEB_CLIENT === $clientType) {
            $cookie = Cookie::create(
                self::REFRESH_TOKEN_COOKIE_NAME,
                $refreshToken->getToken(),
                expire: self::REFRESH_TOKEN_TTL,
                secure: true,
                httpOnly: true,
                sameSite: Cookie::SAMESITE_LAX,
            );

            $response = new JsonResponse(['token' => $jwt]);
            $response->headers->setCookie($cookie);
        } else {
            $response = new JsonResponse([
                'token' => $jwt,
                'refresh_token' => $refreshToken->getToken(),
            ]);
        }

        return $response;
    }
}
