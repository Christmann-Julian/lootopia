<?php

namespace App\Tests\Functional\Controller;

use App\DataFixtures\UserFixtures;
use App\Tests\Trait\FixtureAwareTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RewardControllerTest extends WebTestCase
{
    use FixtureAwareTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();

        $this->addFixture(UserFixtures::class);
        $this->executeFixtures();
    }

    public function testListRewardsAsUser(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $this->client->request('GET', '/api/rewards', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('meta', $content);
    }

    public function testListRewardsAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request(
            'GET',
            '/api/rewards',
            ['page' => 1, 'limit' => 5],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
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
