<?php

namespace App\Tests\Functional\Controller;

use App\DataFixtures\UserFixtures;
use App\Tests\Trait\FixtureAwareTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    use FixtureAwareTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get('security.user_password_hasher');

        $this->addFixture(new UserFixtures($hasher));
        $this->executeFixtures();
    }

    public function testLogin(): void
    {
        $client = $this->client;

        $client->request('POST', '/api/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode([
            'email' => 'admin@lootopia.fr',
            'password' => 'admin',
            'client_type' => 'mobile',
        ]));

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson((string) $client->getResponse()->getContent());
    }
}
