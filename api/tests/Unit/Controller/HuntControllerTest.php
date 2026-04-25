<?php

namespace App\Tests\Unit\Controller;

use App\Controller\HuntController;
use App\Entity\Category;
use App\Entity\Company;
use App\Entity\Hunt;
use App\Entity\Rarity;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\CategoryRepository;
use App\Repository\HuntRepository;
use App\Repository\RarityRepository;
use App\Validator\DtoValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HuntControllerTest extends TestCase
{
    private HuntController $controller;
    private HuntRepository&MockObject $huntRepository;
    private CategoryRepository&MockObject $categoryRepository;
    private RarityRepository&MockObject $rarityRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private DtoValidator&MockObject $dtoValidator;
    private PaginatorInterface&MockObject $paginator;
    private TokenStorageInterface&MockObject $tokenStorage;
    private AuthorizationCheckerInterface&MockObject $authChecker;
    private ContainerInterface&MockObject $container;

    protected function setUp(): void
    {
        $this->huntRepository = $this->createMock(HuntRepository::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->rarityRepository = $this->createMock(RarityRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dtoValidator = $this->createMock(DtoValidator::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);

        $this->container->method('has')->willReturn(true);
        $this->container->method('get')->willReturnMap([
            ['security.token_storage', $this->tokenStorage],
            ['security.authorization_checker', $this->authChecker],
        ]);

        $this->controller = new HuntController();
        $this->controller->setContainer($this->container);
    }

    private function configureSecurity(?User $user, bool $isAdmin): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);

        $this->authChecker->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn($isAdmin);
    }

    private function createHuntMock(int $id): Hunt
    {
        $hunt = new Hunt();
        $hunt->setLat(48.0);
        $hunt->setLon(2.0);

        $reflection = new \ReflectionClass($hunt);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($hunt, $id);

        return $hunt;
    }

    public function testListPublic(): void
    {
        $request = new Request();

        $this->huntRepository->expects($this->once())
            ->method('createPublicListQueryBuilder');

        $response = $this->controller->listPublic($this->huntRepository, $request, $this->paginator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testListAdmin(): void
    {
        $user = new User();
        $company = new Company();
        $user->setCompany($company);
        $this->configureSecurity($user, false);

        $request = new Request(['page' => 1, 'limit' => 10]);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->huntRepository->expects($this->once())
            ->method('createSearchQueryBuilder')
            ->willReturn($queryBuilder);

        $pagination->method('getItems')->willReturn([$this->createHuntMock(1)]);
        $pagination->method('getCurrentPageNumber')->willReturn(1);
        $pagination->method('getItemNumberPerPage')->willReturn(10);
        $pagination->method('getTotalItemCount')->willReturn(1);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->willReturn($pagination);

        $response = $this->controller->listAdmin($this->huntRepository, $request, $this->paginator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertArrayHasKey('meta', $content);
    }

    public function testCreateMissingCompany(): void
    {
        $user = new User();
        $this->configureSecurity($user, false);

        $request = new Request([], [], [], [], [], [], (string) json_encode([]));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('User must belong to a company to create a hunt');

        $this->controller->create(
            $request,
            $this->entityManager,
            $this->dtoValidator,
            $this->categoryRepository,
            $this->rarityRepository
        );
    }

    public function testCreateMissingRarity(): void
    {
        $user = new User();
        $user->setCompany(new Company());
        $this->configureSecurity($user, false);

        $request = new Request([], [], [], [], [], [], (string) json_encode(['rarityId' => 999]));

        $this->rarityRepository->method('find')->willReturn(null);

        $this->expectException(ApiException::class);

        $this->controller->create(
            $request,
            $this->entityManager,
            $this->dtoValidator,
            $this->categoryRepository,
            $this->rarityRepository
        );
    }

    public function testCreateSuccess(): void
    {
        $user = new User();
        $user->setCompany(new Company());
        $this->configureSecurity($user, false);

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'lat' => 48.0,
            'lon' => 2.0,
            'rarityId' => 1,
            'categoryId' => 2,
            'translations' => [
                'fr' => [
                    'title' => 'Chasse au trésor',
                    'description' => 'Trouvez le trésor caché en répondant à la question.',
                    'question' => 'Question ?',
                    'answer' => 'Réponse',
                    'location' => 'Paris',
                ],

                'en' => [
                    'title' => 'Title',
                    'description' => 'Desc',
                    'question' => 'Q',
                    'answer' => 'A',
                    'location' => 'Loc',
                ],
            ],
            'reward' => [
                'code' => 'CODE',
                'translations' => [
                    'fr' => 'Récompense Test',
                    'en' => 'Test Reward',
                ],
            ],
        ]));

        $rarity = new Rarity();
        $category = new Category();

        $this->rarityRepository->method('find')->willReturn($rarity);
        $this->categoryRepository->method('find')->willReturn($category);

        $this->dtoValidator->expects($this->exactly(2))->method('validate');

        $this->entityManager->expects($this->exactly(5))->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->create(
            $request,
            $this->entityManager,
            $this->dtoValidator,
            $this->categoryRepository,
            $this->rarityRepository
        );

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testUpdateForbidden(): void
    {
        $user = new User();
        $user->setCompany(new Company());
        $this->configureSecurity($user, false);

        $hunt = clone $this->createHuntMock(1);
        $hunt->setCompany(new Company());

        $request = new Request([], [], [], [], [], [], (string) json_encode([]));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('You cannot edit this hunt');

        $this->controller->update(
            $hunt,
            $request,
            $this->entityManager,
            $this->dtoValidator,
            $this->categoryRepository,
            $this->rarityRepository
        );
    }

    public function testUpdateSuccess(): void
    {
        $company = new Company();
        $user = new User();
        $user->setCompany($company);
        $this->configureSecurity($user, false);

        $hunt = clone $this->createHuntMock(1);
        $hunt->setCompany($company);
        $hunt->setRarity(new Rarity());

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'rarityId' => 1,
            'translations' => [
                'fr' => [
                    'title' => 'Chasse au trésor Modifiée',
                    'description' => 'Trouvez le trésor caché en répondant à la question.',
                    'question' => 'Question ?',
                    'answer' => 'Réponse',
                    'location' => 'Lyon',
                ],
                'en' => [
                    'title' => 'New Title',
                    'description' => 'New Desc',
                    'question' => 'New Q',
                    'answer' => 'New A',
                    'location' => 'New Loc',
                ],
            ],
        ]));

        $this->rarityRepository->method('find')->willReturn(new Rarity());

        $this->dtoValidator->expects($this->once())->method('validate');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->update(
            $hunt,
            $request,
            $this->entityManager,
            $this->dtoValidator,
            $this->categoryRepository,
            $this->rarityRepository
        );

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testDeleteForbidden(): void
    {
        $user = new User();
        $user->setCompany(new Company());
        $this->configureSecurity($user, false);

        $hunt = clone $this->createHuntMock(1);
        $hunt->setCompany(new Company());

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('You cannot delete this hunt');

        $this->controller->delete($hunt, $this->entityManager);
    }

    public function testDeleteSuccess(): void
    {
        $company = new Company();
        $user = new User();
        $user->setCompany($company);
        $this->configureSecurity($user, false);

        $hunt = clone $this->createHuntMock(1);
        $hunt->setCompany($company);

        $this->entityManager->expects($this->once())->method('remove')->with($hunt);
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->delete($hunt, $this->entityManager);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
