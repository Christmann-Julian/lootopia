<?php

namespace App\Tests\Functional\Controller;

use App\DataFixtures\CategoryFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Category;
use App\Tests\Trait\FixtureAwareTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CategoryControllerTest extends WebTestCase
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
        $this->addFixture(new CategoryFixtures());
        $this->executeFixtures();
    }

    public function testListCategoriesAsUser(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $this->client->request('GET', '/api/categories', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
    }

    public function testAdminListCategoriesAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request(
            'GET',
            '/api/categories/admin',
            ['page' => 1, 'limit' => 5],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('meta', $content);
    }

    public function testAdminListCategoriesAsUserForbidden(): void
    {
        $token = $this->getJwtToken('user@lootopia.fr', 'user');

        $this->client->request('GET', '/api/categories/admin', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateCategoryAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $this->client->request(
            'POST',
            '/api/categories',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'icon' => 'fa-shield',
                'translations' => [
                    'fr' => 'Bouclier',
                    'en' => 'Shield',
                ],
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $createdCategory = $this->getRepository(Category::class)->findOneBy(['icon' => 'fa-shield']);
        $this->assertNotNull($createdCategory);
        $this->assertCount(2, $createdCategory->getCategoryTranslations());
    }

    public function testUpdateCategoryAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $category = $this->getRepository(Category::class)->findOneBy([]);
        if (!$category) {
            throw new \RuntimeException('No category found in database for testing.');
        }

        $this->client->request(
            'PUT',
            '/api/categories/'.$category->getId(),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            (string) json_encode([
                'icon' => 'fa-sword',
                'translations' => [
                    'fr' => 'Épée',
                    'en' => 'Sword',
                ],
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->getEntityManager()->clear();
        $updatedCategory = $this->getRepository(Category::class)->find($category->getId());

        $this->assertNotNull($updatedCategory);
        $this->assertEquals('fa-sword', $updatedCategory->getIcon());
    }

    public function testDeleteCategoryAsAdmin(): void
    {
        $token = $this->getJwtToken('admin@lootopia.fr', 'admin');

        $category = $this->getRepository(Category::class)->findOneBy([]);
        if (!$category) {
            throw new \RuntimeException('No category found in database for testing.');
        }

        $id = $category->getId();

        $this->client->request(
            'DELETE',
            '/api/categories/'.$id,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $deletedCategory = $this->getRepository(Category::class)->find($id);
        $this->assertNull($deletedCategory);
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
