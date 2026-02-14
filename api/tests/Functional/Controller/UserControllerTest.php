<?php

namespace App\Tests\Functional\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Tests\Trait\FixtureAwareTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
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

        $this->addFixture(new UserFixtures($this->hasher));
        $this->executeFixtures();
    }

    public function testListUsersAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request(
            'GET',
            '/api/users',
            ['page' => 1, 'limit' => 5],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('meta', $content);
        $this->assertGreaterThanOrEqual(1, count($content['data']));
    }

    public function testListUsersAsUserForbidden(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $this->client->request(
            'GET',
            '/api/users',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testGetUserDetails(): void
    {
        $adminToken = $this->getJwtToken('admin@lootopia.fr', 'admin');
        $userToken = $this->getJwtToken('user@lootopia.fr', 'user');

        $targetUser = $this->getRepository(User::class)->findOneBy(['email' => 'user@lootopia.fr']);

        if (!$targetUser) {
            throw new \RuntimeException('User de test introuvable en base');
        }

        $this->client->request('GET', '/api/users/'.$targetUser->getId(), [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$adminToken]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->client->request('GET', '/api/users/'.$targetUser->getId(), [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$userToken]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testGetOtherUserDetailsForbidden(): void
    {
        $userToken = $this->getJwtToken('user@lootopia.fr', 'user');
        $adminUser = $this->getRepository(User::class)->findOneBy(['email' => 'admin@lootopia.fr']);

        if (!$adminUser) {
            throw new \RuntimeException('Admin user de test introuvable en base');
        }

        $this->client->request(
            'GET',
            '/api/users/'.$adminUser->getId(),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$userToken]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateUser(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'firstname' => 'New',
                'lastname' => 'AdminCreated',
                'email' => 'created@test.com',
                'password' => 'Password123!',
                'roles' => ['ROLE_USER'],
                'isVerified' => true,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Vérification en base
        $createdUser = $this->getRepository(User::class)->findOneBy(['email' => 'created@test.com']);
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->isVerified());
    }

    public function testUpdateSelf(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');
        $user = $this->getRepository(User::class)->findOneBy(['email' => 'user@lootopia.fr']);

        if (!$user) {
            throw new \RuntimeException('User de test introuvable en base');
        }

        $this->client->request(
            'PUT',
            '/api/users/'.$user->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'firstname' => 'UpdatedName',
                'lastname' => 'UpdatedLast',
                'email' => 'user@lootopia.fr',
                'roles' => ['ROLE_ADMIN'],
                'isVerified' => true,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->getEntityManager()->clear();
        $updatedUser = $this->getRepository(User::class)->find($user->getId());

        $this->assertNotNull($updatedUser);
        $this->assertEquals('UpdatedName', $updatedUser->getFirstname());
        $this->assertNotContains('ROLE_ADMIN', $updatedUser->getRoles());
    }

    public function testUpdatePassword(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');
        $user = $this->getRepository(User::class)->findOneBy(['email' => 'user@lootopia.fr']);

        if (!$user) {
            throw new \RuntimeException('User de test introuvable en base');
        }

        $newPassword = 'NewSecurePassword123!';

        $this->client->request(
            'PUT',
            '/api/users/'.$user->getId().'/password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'currentPassword' => 'user',
                'newPassword' => $newPassword,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode(['email' => 'user@lootopia.fr', 'password' => $newPassword, 'client_type' => 'web'])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDeleteUserAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');
        $targetUser = $this->getRepository(User::class)->findOneBy(['email' => 'user@lootopia.fr']);

        if (!$targetUser) {
            throw new \RuntimeException('User de test introuvable en base');
        }

        $id = $targetUser->getId();

        $this->client->request(
            'DELETE',
            '/api/users/'.$id,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $deletedUser = $this->getRepository(User::class)->find($id);
        $this->assertNull($deletedUser);
    }

    private function getJwtToken(string $email, string $password): string
    {
        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => $email,
                'password' => $password,
                'client_type' => 'web',
            ])
        );

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        if (!isset($data['token'])) {
            throw new \RuntimeException(sprintf('Impossible de loguer %s. Réponse: %s', $email, $this->client->getResponse()->getContent()));
        }

        return $data['token'];
    }
}
