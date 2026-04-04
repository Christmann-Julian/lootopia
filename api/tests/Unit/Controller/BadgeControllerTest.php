<?php

namespace App\Tests\Unit\Controller;

use App\Controller\BadgeController;
use App\Dto\Badge\CreateBadgeRequest;
use App\Dto\Badge\UpdateBadgeRequest;
use App\Entity\Badge;
use App\Entity\BadgeTranslation;
use App\Repository\BadgeRepository;
use App\Validator\DtoValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BadgeControllerTest extends TestCase
{
    private BadgeController $controller;
    private BadgeRepository&MockObject $badgeRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private DtoValidator&MockObject $dtoValidator;
    private PaginatorInterface&MockObject $paginator;

    protected function setUp(): void
    {
        $this->badgeRepository = $this->createMock(BadgeRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dtoValidator = $this->createMock(DtoValidator::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);

        $this->controller = new BadgeController();
    }

    private function createBadgeMock(int $id, string $icon): Badge
    {
        $badge = new Badge();
        $badge->setIcon($icon);
        
        $reflection = new \ReflectionClass($badge);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($badge, $id);

        return $badge;
    }

    public function testList(): void
    {
        $request = new Request(['locale' => 'fr']);
        
        $badge = clone $this->createBadgeMock(1, 'fa-star');
        
        $this->badgeRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$badge]);

        $response = $this->controller->list($this->badgeRepository, $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertCount(1, $content['data']);
    }

    public function testAdminList(): void
    {
        $request = new Request(['page' => 1, 'limit' => 10, 'q' => 'star']);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->badgeRepository->expects($this->once())
            ->method('createSearchQueryBuilder')
            ->with('star', 'relevance', 'asc')
            ->willReturn($queryBuilder);

        $pagination->method('getItems')->willReturn([
            $this->createBadgeMock(1, 'fa-star')
        ]);
        $pagination->method('getCurrentPageNumber')->willReturn(1);
        $pagination->method('getItemNumberPerPage')->willReturn(10);
        $pagination->method('getTotalItemCount')->willReturn(1);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->willReturn($pagination);

        $response = $this->controller->adminList($this->badgeRepository, $request, $this->paginator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('meta', $content);
        $this->assertEquals(1, $content['meta']['total']);
    }

    public function testGetDetails(): void
    {
        $request = new Request(['locale' => 'en']);
        $badge = $this->createBadgeMock(1, 'fa-medal');

        $response = $this->controller->getDetails($badge, $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('fa-medal', $content['icon']);
    }

    public function testCreateSuccess(): void
    {
        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'icon' => 'fa-trophy',
            'translations' => ['fr' => 'Trophée']
        ]));

        $this->dtoValidator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(CreateBadgeRequest::class));

        $this->entityManager->expects($this->exactly(2))->method('persist'); 
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->create($request, $this->entityManager, $this->dtoValidator);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testUpdateSuccess(): void
    {
        $badge = clone $this->createBadgeMock(1, 'fa-old');
        $existingTranslation = new BadgeTranslation();
        $existingTranslation->setLocale('fr')->setName('Ancien nom');
        $badge->addBadgeTranslation($existingTranslation);

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'icon' => 'fa-new',
            'translations' => [
                'fr' => 'Nouveau nom',
                'en' => 'New name'
            ]
        ]));

        $this->dtoValidator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(UpdateBadgeRequest::class));

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->update($badge, $request, $this->entityManager, $this->dtoValidator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('fa-new', $badge->getIcon());

        $this->assertEquals('Nouveau nom', $badge->getTranslation('fr')->getName());
    }

    public function testDeleteSuccess(): void
    {
        $badge = $this->createBadgeMock(1, 'fa-trash');

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($badge);
        
        $this->entityManager->expects($this->once())
            ->method('flush');

        $response = $this->controller->delete($badge, $this->entityManager);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}