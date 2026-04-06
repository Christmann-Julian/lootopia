<?php

namespace App\Tests\Functional\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Tests\Trait\FixtureAwareTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    use FixtureAwareTrait;

    private KernelBrowser $client;
    private UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = self::getContainer()->get('security.user_password_hasher');
        $this->hasher = $hasher;

        $this->addFixture(UserFixtures::class);
        $this->executeFixtures();
    }

    public function testLoginSuccess(): void
    {
        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => 'admin@lootopia.fr',
                'password' => 'admin',
                'client_type' => 'mobile',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode((string) $response->getContent(), true);

        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
    }

    public function testLoginFailureInvalidCredentials(): void
    {
        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => 'admin@lootopia.fr',
                'password' => 'mauvais_password',
                'client_type' => 'mobile',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testMe(): void
    {
        $token = $this->getJwtToken();

        $this->client->request(
            'GET',
            '/api/auth/me',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        $this->assertEquals('admin@lootopia.fr', $data['email']);
    }

    public function testMeUnauthorized(): void
    {
        $this->client->request('GET', '/api/auth/me');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testRegister(): void
    {
        $email = 'newuser@lootopia.fr';

        $this->client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'firstname' => 'New',
                'lastname' => 'User',
                'pseudo' => 'NewUser',
                'company' => 'Test Corp',
                'email' => $email,
                'password' => 'Password123!',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $user = $this->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->assertNotNull($user);
        $this->assertFalse($user->isVerified());
    }

    public function testLoginUnverifiedUser(): void
    {
        $user = new User();
        $user->setEmail('unverified@test.com')
            ->setPseudo('UnverifiedUser')
            ->setExperience(0)
            ->setHuntCount(0)
            ->setRewardCount(0)
            ->setFirstname('John')->setLastname('Doe')
            ->setPassword($this->hasher->hashPassword($user, 'password'))
            ->setIsVerified(false);

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => 'unverified@test.com',
                'password' => 'password',
                'client_type' => 'mobile',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRefreshToken(): void
    {
        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => 'admin@lootopia.fr',
                'password' => 'admin',
                'client_type' => 'mobile',
            ])
        );
        $loginData = json_decode((string) $this->client->getResponse()->getContent(), true);
        $refreshToken = $loginData['refresh_token'];
        $firstJwt = $loginData['token'];

        sleep(1);

        $this->client->request(
            'POST',
            '/api/auth/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'refresh_token' => $refreshToken,
                'client_type' => 'mobile',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $refreshData = json_decode((string) $this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $refreshData);

        $this->assertNotEquals($firstJwt, $refreshData['token']);
    }

    public function testLogout(): void
    {
        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => 'admin@lootopia.fr',
                'password' => 'admin',
                'client_type' => 'mobile',
            ])
        );
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $refreshToken = $data['refresh_token'];

        $this->client->request(
            'POST',
            '/api/auth/logout',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode(['refresh_token' => $refreshToken])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->request(
            'POST',
            '/api/auth/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'refresh_token' => $refreshToken,
                'client_type' => 'mobile',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testRequestPasswordReset(): void
    {
        $this->client->request(
            'POST',
            '/api/auth/password/reset/request',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode(['email' => 'admin@lootopia.fr'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);

        $token = $this->getRepository(PasswordResetToken::class)->findAll();
        $this->assertNotEmpty($token);
    }

    public function testResetPassword(): void
    {
        $em = $this->getEntityManager();
        $user = $this->getRepository(User::class)->findOneBy(['email' => 'admin@lootopia.fr']);

        $tokenString = 'valid_token_123';
        $resetToken = new PasswordResetToken();
        $resetToken->setUser($user)
            ->setToken($tokenString)
            ->setExpiresAt(new \DateTimeImmutable('+1 hour'))
            ->setIpAddress('127.0.0.1')
            ->setUserAgent('Symfony Browser');

        $em->persist($resetToken);
        $em->flush();

        $newPassword = 'NewPasswordSecure123!';
        $this->client->request(
            'POST',
            '/api/auth/password/reset',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'token' => $tokenString,
                'password' => $newPassword,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => 'admin@lootopia.fr',
                'password' => $newPassword,
                'client_type' => 'mobile',
            ])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    private function getJwtToken(): string
    {
        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => 'admin@lootopia.fr',
                'password' => 'admin',
                'client_type' => 'mobile',
            ])
        );

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        return $data['token'];
    }
}
