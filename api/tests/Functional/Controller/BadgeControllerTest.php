<?php

namespace App\Tests\Functional\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\Badge;
use App\Entity\User;
use App\DataFixtures\BadgeFixtures;
use App\Tests\Trait\FixtureAwareTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BadgeControllerTest extends WebTestCase
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
        $this->addFixture(new BadgeFixtures());
        $this->executeFixtures();
    }

    public function testListBadgesAsUser(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $this->client->request('GET', '/api/badges', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
    }

    public function testAdminListBadgesAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request(
            'GET',
            '/api/badges/admin',
            ['page' => 1, 'limit' => 5],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('meta', $content);
    }

    public function testAdminListBadgesAsUserForbidden(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $this->client->request('GET', '/api/badges/admin', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateBadgeAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request(
            'POST',
            '/api/badges',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'icon' => 'fa-star',
                'translations' => [
                    'fr' => 'Étoile',
                    'en' => 'Star'
                ]
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $createdBadge = $this->getRepository(Badge::class)->findOneBy(['icon' => 'fa-star']);
        $this->assertNotNull($createdBadge);
        $this->assertCount(2, $createdBadge->getBadgeTranslations());
    }

    public function testUpdateBadgeAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $badge = $this->getRepository(Badge::class)->findOneBy([]);
        if (!$badge) {
            throw new \RuntimeException('No badge found in database for testing.');
        }

        $this->client->request(
            'PUT',
            '/api/badges/'.$badge->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'icon' => 'fa-updated',
                'translations' => [
                    'fr' => 'Mis à jour',
                    'en' => 'Updated'
                ]
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->getEntityManager()->clear();
        $updatedBadge = $this->getRepository(Badge::class)->find($badge->getId());

        $this->assertNotNull($updatedBadge);
        $this->assertEquals('fa-updated', $updatedBadge->getIcon());
    }

    public function testDeleteBadgeAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');
        
        $badge = $this->getRepository(Badge::class)->findOneBy([]);
        if (!$badge) {
            throw new \RuntimeException('No badge found in database for testing.');
        }

        $id = $badge->getId();

        $this->client->request(
            'DELETE',
            '/api/badges/'.$id,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $deletedBadge = $this->getRepository(Badge::class)->find($id);
        $this->assertNull($deletedBadge);
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
            throw new \RuntimeException(sprintf('Impossible to login %s. Response: %s', $email, $this->client->getResponse()->getContent()));
        }

        return $data['token'];
    }
}