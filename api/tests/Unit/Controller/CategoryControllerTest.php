<?php

namespace App\Tests\Unit\Controller;

use App\Controller\CategoryController;
use App\Dto\Category\CreateCategoryRequest;
use App\Dto\Category\UpdateCategoryRequest;
use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Repository\CategoryRepository;
use App\Validator\DtoValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryControllerTest extends TestCase
{
    private CategoryController $controller;
    private CategoryRepository&MockObject $categoryRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private DtoValidator&MockObject $dtoValidator;
    private PaginatorInterface&MockObject $paginator;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dtoValidator = $this->createMock(DtoValidator::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);

        $this->controller = new CategoryController();
    }

    private function createCategoryMock(int $id, string $icon): Category
    {
        $category = new Category();
        $category->setIcon($icon);

        $reflection = new \ReflectionClass($category);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($category, $id);

        return $category;
    }

    public function testList(): void
    {
        $request = new Request(['locale' => 'fr']);

        $category = clone $this->createCategoryMock(1, 'fa-shield');

        $this->categoryRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$category]);

        $response = $this->controller->list($this->categoryRepository, $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertCount(1, $content['data']);
    }

    public function testAdminList(): void
    {
        $request = new Request(['page' => 1, 'limit' => 10, 'q' => 'shield']);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->categoryRepository->expects($this->once())
            ->method('createSearchQueryBuilder')
            ->with('shield', 'relevance', 'asc')
            ->willReturn($queryBuilder);

        $pagination->method('getItems')->willReturn([
            $this->createCategoryMock(1, 'fa-shield'),
        ]);
        $pagination->method('getCurrentPageNumber')->willReturn(1);
        $pagination->method('getItemNumberPerPage')->willReturn(10);
        $pagination->method('getTotalItemCount')->willReturn(1);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->willReturn($pagination);

        $response = $this->controller->adminList($this->categoryRepository, $request, $this->paginator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertArrayHasKey('meta', $content);
        $this->assertEquals(1, $content['meta']['total']);
    }

    public function testGetDetails(): void
    {
        $request = new Request(['locale' => 'en']);
        $category = $this->createCategoryMock(1, 'fa-sword');

        $response = $this->controller->getDetails($category, $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertEquals('fa-sword', $content['icon']);
    }

    public function testCreateSuccess(): void
    {
        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'icon' => 'fa-bow',
            'translations' => ['fr' => 'Arc'],
        ]));

        $this->dtoValidator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(CreateCategoryRequest::class));

        $this->entityManager->expects($this->exactly(2))->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->create($request, $this->entityManager, $this->dtoValidator);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testUpdateSuccess(): void
    {
        $category = clone $this->createCategoryMock(1, 'fa-old');
        $existingTranslation = new CategoryTranslation();
        $existingTranslation->setLocale('fr')->setName('Old name');
        $category->addCategoryTranslation($existingTranslation);

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'icon' => 'fa-new',
            'translations' => [
                'fr' => 'New name',
                'en' => 'New name EN',
            ],
        ]));

        $this->dtoValidator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(UpdateCategoryRequest::class));

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->update($category, $request, $this->entityManager, $this->dtoValidator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('fa-new', $category->getIcon());
        $this->assertEquals('New name', $category->getTranslation('fr')?->getName());
    }

    public function testDeleteSuccess(): void
    {
        $category = $this->createCategoryMock(1, 'fa-trash');

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($category);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $response = $this->controller->delete($category, $this->entityManager);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
