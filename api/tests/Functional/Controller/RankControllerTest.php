<?php

namespace App\Tests\Functional\Controller;

use App\DataFixtures\RankFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Rank;
use App\Tests\Trait\FixtureAwareTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RankControllerTest extends WebTestCase
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
        $this->addFixture(new RankFixtures());
        $this->executeFixtures();
    }

    public function testListRanksAsUser(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $this->client->request('GET', '/api/ranks', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
    }

    public function testAdminListRanksAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request(
            'GET',
            '/api/ranks/admin',
            ['page' => 1, 'limit' => 5],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('meta', $content);
    }

    public function testAdminListRanksAsUserForbidden(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $this->client->request('GET', '/api/ranks/admin', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateRankAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request(
            'POST',
            '/api/ranks',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'experienceMin' => 0,
                'experienceMax' => 99,
                'level' => 1,
                'translations' => [
                    'fr' => 'Débutant',
                    'en' => 'Beginner',
                ],
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $createdRank = $this->getRepository(Rank::class)->findOneBy(['level' => 1]);
        $this->assertNotNull($createdRank);
        $this->assertEquals(0, $createdRank->getExperienceMin());
        $this->assertCount(2, $createdRank->getRankTranslations());
    }

    public function testUpdateRankAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $rank = $this->getRepository(Rank::class)->findOneBy([]);
        if (!$rank) {
            throw new \RuntimeException('No rank found in database for testing.');
        }

        $this->client->request(
            'PUT',
            '/api/ranks/'.$rank->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'experienceMin' => 100,
                'experienceMax' => 299,
                'level' => 2,
                'translations' => [
                    'fr' => 'Initié',
                    'en' => 'Initiate',
                ],
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->getEntityManager()->clear();
        $updatedRank = $this->getRepository(Rank::class)->find($rank->getId());

        $this->assertNotNull($updatedRank);
        $this->assertEquals(2, $updatedRank->getLevel());
        $this->assertEquals(100, $updatedRank->getExperienceMin());
    }

    public function testDeleteRankAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $rank = $this->getRepository(Rank::class)->findOneBy([]);
        if (!$rank) {
            throw new \RuntimeException('No rank found in database for testing.');
        }

        $id = $rank->getId();

        $this->client->request(
            'DELETE',
            '/api/ranks/'.$id,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $deletedRank = $this->getRepository(Rank::class)->find($id);
        $this->assertNull($deletedRank);
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
            throw new \RuntimeException(sprintf('Cannot login %s. Response: %s', $email, $this->client->getResponse()->getContent()));
        }

        return $data['token'];
    }
}
