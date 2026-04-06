<?php

namespace App\Tests\Unit\Controller;

use App\Controller\RankController;
use App\Dto\Rank\CreateRankRequest;
use App\Dto\Rank\UpdateRankRequest;
use App\Entity\Rank;
use App\Entity\RankTranslation;
use App\Repository\RankRepository;
use App\Validator\DtoValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RankControllerTest extends TestCase
{
    private RankController $controller;
    private RankRepository&MockObject $rankRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private DtoValidator&MockObject $dtoValidator;
    private PaginatorInterface&MockObject $paginator;

    protected function setUp(): void
    {
        $this->rankRepository = $this->createMock(RankRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dtoValidator = $this->createMock(DtoValidator::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);

        $this->controller = new RankController();
    }

    private function createRankMock(int $id, int $level): Rank
    {
        $rank = new Rank();
        $rank->setLevel($level);
        $rank->setExperienceMin(0);
        $rank->setExperienceMax(100);

        $reflection = new \ReflectionClass($rank);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($rank, $id);

        return $rank;
    }

    public function testList(): void
    {
        $request = new Request(['locale' => 'fr']);

        $rank = clone $this->createRankMock(1, 1);

        $this->rankRepository->expects($this->once())
            ->method('findBy')
            ->with([], ['level' => 'ASC'])
            ->willReturn([$rank]);

        $response = $this->controller->list($this->rankRepository, $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertCount(1, $content['data']);
    }

    public function testAdminList(): void
    {
        $request = new Request(['page' => 1, 'limit' => 10, 'q' => 'novice']);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->rankRepository->expects($this->once())
            ->method('createSearchQueryBuilder')
            ->with('novice', 'relevance', 'asc')
            ->willReturn($queryBuilder);

        $pagination->method('getItems')->willReturn([
            $this->createRankMock(1, 1),
        ]);
        $pagination->method('getCurrentPageNumber')->willReturn(1);
        $pagination->method('getItemNumberPerPage')->willReturn(10);
        $pagination->method('getTotalItemCount')->willReturn(1);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->willReturn($pagination);

        $response = $this->controller->adminList($this->rankRepository, $request, $this->paginator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertArrayHasKey('meta', $content);
        $this->assertEquals(1, $content['meta']['total']);
    }

    public function testGetDetails(): void
    {
        $request = new Request(['locale' => 'en']);
        $rank = $this->createRankMock(1, 2);

        $response = $this->controller->getDetails($rank, $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testCreateSuccess(): void
    {
        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'experienceMin' => 0,
            'experienceMax' => 99,
            'level' => 1,
            'translations' => ['fr' => 'Débutant', 'en' => 'Beginner'],
        ]));

        $this->dtoValidator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(CreateRankRequest::class));

        $this->entityManager->expects($this->exactly(3))->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->create($request, $this->entityManager, $this->dtoValidator);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testUpdateSuccess(): void
    {
        $rank = clone $this->createRankMock(1, 1);
        $existingTranslation = new RankTranslation();
        $existingTranslation->setLocale('fr')->setName('Ancien rang');
        $rank->addRankTranslation($existingTranslation);

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'experienceMin' => 100,
            'experienceMax' => 200,
            'level' => 2,
            'translations' => [
                'fr' => 'Nouveau rang fr',
                'en' => 'New rank en',
            ],
        ]));

        $this->dtoValidator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(UpdateRankRequest::class));

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->update($rank, $request, $this->entityManager, $this->dtoValidator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(2, $rank->getLevel());
        $this->assertEquals('Nouveau rang fr', $rank->getTranslation('fr')?->getName());
    }

    public function testDeleteSuccess(): void
    {
        $rank = $this->createRankMock(1, 1);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($rank);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $response = $this->controller->delete($rank, $this->entityManager);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
