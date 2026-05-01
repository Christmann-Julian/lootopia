<?php

namespace App\Tests\Functional\Controller;

use App\DataFixtures\CategoryFixtures;
use App\DataFixtures\HuntFixtures;
use App\DataFixtures\RarityFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Category;
use App\Entity\Hunt;
use App\Entity\Rarity;
use App\Tests\Trait\FixtureAwareTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HuntControllerTest extends WebTestCase
{
    use FixtureAwareTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();

        $container = self::getContainer();

        $this->addFixture(UserFixtures::class);
        $this->addFixture(CategoryFixtures::class);
        $this->addFixture(RarityFixtures::class);
        $this->addFixture(HuntFixtures::class);

        $this->executeFixtures();
    }

    public function testListPublicHunts(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $this->client->request('GET', '/api/hunts', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
    }

    public function testAdminListHuntsAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request(
            'GET',
            '/api/hunts/admin',
            ['page' => 1, 'limit' => 5],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('meta', $content);
    }

    public function testGetHuntDetails(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $hunt = $this->getRepository(Hunt::class)->findOneBy([]);
        if (!$hunt) {
            $this->markTestSkipped('No hunt found in database for testing.');
        }

        $this->client->request(
            'GET',
            '/api/hunts/'.$hunt->getId(),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testCreateHuntAsUser(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $rarity = $this->getRepository(Rarity::class)->findOneBy([]);
        $category = $this->getRepository(Category::class)->findOneBy([]);

        if (!$rarity || !$category) {
            $this->markTestSkipped('Missing Rarity or Category in database for testing.');
        }

        $this->client->request(
            'POST',
            '/api/hunts',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'lat' => 48.8566,
                'lon' => 2.3522,
                'categoryId' => $category->getId(),
                'rarityId' => $rarity->getId(),
                'translations' => [
                    'fr' => [
                        'title' => 'Chasse Test',
                        'description' => 'Description test',
                        'question' => 'Question ?',
                        'answer' => 'Réponse',
                        'location' => 'Paris',
                    ],
                    'en' => [
                        'title' => 'Test Hunt',
                        'description' => 'Test description',
                        'question' => 'Question?',
                        'answer' => 'Answer',
                        'location' => 'Paris',
                    ],
                ],
                'reward' => [
                    'code' => 'TESTPROMO',
                    'link' => 'https://example.com/test',
                    'endDate' => '2025-12-31T23:59',
                    'translations' => [
                        'fr' => 'Récompense Test',
                        'en' => 'Test Reward',
                    ],
                ],
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testUpdateHuntAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $hunt = $this->getRepository(Hunt::class)->findOneBy([]);
        $rarity = $this->getRepository(Rarity::class)->findOneBy([]);

        if (!$hunt || !$rarity) {
            $this->markTestSkipped('No hunt or rarity found in database for testing.');
        }

        $this->client->request(
            'PUT',
            '/api/hunts/'.$hunt->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'lat' => 45.0,
                'lon' => 4.0,
                'rarityId' => $rarity->getId(),
                'translations' => [
                    'fr' => [
                        'title' => 'Chasse Modifiée',
                        'description' => 'Modifié',
                        'question' => 'Q?',
                        'answer' => 'R',
                        'location' => 'Lyon',
                    ],
                    'en' => [
                        'title' => 'Modified Hunt',
                        'description' => 'Modified',
                        'question' => 'Q?',
                        'answer' => 'A',
                        'location' => 'Lyon',
                    ],
                ],
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->getEntityManager()->clear();
        $updatedHunt = $this->getRepository(Hunt::class)->find($hunt->getId());

        $this->assertNotNull($updatedHunt);
        $this->assertEquals(45.0, $updatedHunt->getLat());
    }

    public function testDeleteHuntAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $hunt = $this->getRepository(Hunt::class)->findOneBy([]);
        if (!$hunt) {
            $this->markTestSkipped('No hunt found in database for testing.');
        }

        $id = $hunt->getId();

        $this->client->request(
            'DELETE',
            '/api/hunts/'.$id,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $deletedHunt = $this->getRepository(Hunt::class)->find($id);
        $this->assertNull($deletedHunt);
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
