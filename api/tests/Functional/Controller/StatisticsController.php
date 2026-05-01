<?php

namespace App\Tests\Functional\Controller;

use App\DataFixtures\UserFixtures;
use App\Tests\Trait\FixtureAwareTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class StatisticsControllerTest extends WebTestCase
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

    public function testAdminStatsAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request('GET', '/api/statistics/admin', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('totalUsers', $content);
        $this->assertArrayHasKey('totalHunts', $content);
        $this->assertArrayHasKey('totalCompanies', $content);
    }

    public function testAdminStatsAsUserForbidden(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $this->client->request('GET', '/api/statistics/admin', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCompanyStatsAsCompanyUser(): void
    {
        $token = $this->getJwtToken('company@lootopia.fr', 'password123');

        $this->client->request('GET', '/api/statistics/company', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $responseCode = $this->client->getResponse()->getStatusCode();

        if (Response::HTTP_OK === $responseCode) {
            $content = json_decode((string) $this->client->getResponse()->getContent(), true);
            $this->assertArrayHasKey('totalHuntsCreated', $content);
            $this->assertArrayHasKey('totalUniqueParticipants', $content);
            $this->assertArrayHasKey('totalRewardsClaimed', $content);
        } else {
            $this->assertEquals(Response::HTTP_FORBIDDEN, $responseCode);
        }
    }

    public function testAdminChartsAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request('GET', '/api/statistics/admin/charts?locale=fr', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('registrations', $content);
        $this->assertArrayHasKey('categoryDistribution', $content);
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
