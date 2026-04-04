<?php

namespace App\Tests\Functional\Controller;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\RarityFixtures;
use App\Entity\Rarity;
use App\Tests\Trait\FixtureAwareTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RarityControllerTest extends WebTestCase
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
        $this->addFixture(new RarityFixtures());
        $this->executeFixtures();
    }

    public function testListRaritiesAsUser(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $this->client->request('GET', '/api/rarities', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
    }

    public function testAdminListRaritiesAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request(
            'GET',
            '/api/rarities/admin',
            ['page' => 1, 'limit' => 5],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('meta', $content);
    }

    public function testAdminListRaritiesAsUserForbidden(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $this->client->request('GET', '/api/rarities/admin', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateRarityAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request(
            'POST',
            '/api/rarities',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'minExperience' => 500000000,
                'experienceGain' => 50,
                'translations' => [
                    'fr' => 'Légendaire',
                    'en' => 'Legendary'
                ]
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $createdRarity = $this->getRepository(Rarity::class)->findOneBy(['minExperience' => 500000000]);
        $this->assertNotNull($createdRarity);
        $this->assertEquals(50, $createdRarity->getExperienceGain());
        $this->assertCount(2, $createdRarity->getRarityTranslations());
    }

    public function testUpdateRarityAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');
        
        $rarity = $this->getRepository(Rarity::class)->findOneBy([]);
        if (!$rarity) {
            throw new \RuntimeException('No rarity found in database for testing.');
        }

        $this->client->request(
            'PUT',
            '/api/rarities/'.$rarity->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'minExperience' => 1000,
                'experienceGain' => 100,
                'translations' => [
                    'fr' => 'Mythique',
                    'en' => 'Mythic'
                ]
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->getEntityManager()->clear();
        $updatedRarity = $this->getRepository(Rarity::class)->find($rarity->getId());

        $this->assertNotNull($updatedRarity);
        $this->assertEquals(1000, $updatedRarity->getMinExperience());
        $this->assertEquals(100, $updatedRarity->getExperienceGain());
    }

    public function testDeleteRarityAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');
        
        $rarity = $this->getRepository(Rarity::class)->findOneBy([]);
        if (!$rarity) {
            throw new \RuntimeException('No rarity found in database for testing.');
        }

        $id = $rarity->getId();

        $this->client->request(
            'DELETE',
            '/api/rarities/'.$id,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $deletedRarity = $this->getRepository(Rarity::class)->find($id);
        $this->assertNull($deletedRarity);
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