<?php

namespace App\Tests\Unit\Controller;

use App\Controller\RarityController;
use App\Dto\Rarity\CreateRarityRequest;
use App\Dto\Rarity\UpdateRarityRequest;
use App\Entity\Rarity;
use App\Entity\RarityTranslation;
use App\Repository\RarityRepository;
use App\Validator\DtoValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RarityControllerTest extends TestCase
{
    private RarityController $controller;
    private RarityRepository&MockObject $rarityRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private DtoValidator&MockObject $dtoValidator;
    private PaginatorInterface&MockObject $paginator;

    protected function setUp(): void
    {
        $this->rarityRepository = $this->createMock(RarityRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dtoValidator = $this->createMock(DtoValidator::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);

        $this->controller = new RarityController();
    }

    private function createRarityMock(int $id, int $minExperience, int $experienceGain): Rarity
    {
        $rarity = new Rarity();
        $rarity->setMinExperience($minExperience);
        $rarity->setExperienceGain($experienceGain);
        
        $reflection = new \ReflectionClass($rarity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($rarity, $id);

        return $rarity;
    }

    public function testList(): void
    {
        $request = new Request(['locale' => 'fr']);
        
        $rarity = clone $this->createRarityMock(1, 100, 10);
        
        $this->rarityRepository->expects($this->once())
            ->method('findBy')
            ->with([], ['minExperience' => 'ASC'])
            ->willReturn([$rarity]);

        $response = $this->controller->list($this->rarityRepository, $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertCount(1, $content['data']);
    }

    public function testAdminList(): void
    {
        $request = new Request(['page' => 1, 'limit' => 10, 'q' => 'rare']);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->rarityRepository->expects($this->once())
            ->method('createSearchQueryBuilder')
            ->with('rare', 'relevance', 'asc')
            ->willReturn($queryBuilder);

        $pagination->method('getItems')->willReturn([
            $this->createRarityMock(1, 100, 10)
        ]);
        $pagination->method('getCurrentPageNumber')->willReturn(1);
        $pagination->method('getItemNumberPerPage')->willReturn(10);
        $pagination->method('getTotalItemCount')->willReturn(1);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->willReturn($pagination);

        $response = $this->controller->adminList($this->rarityRepository, $request, $this->paginator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('meta', $content);
        $this->assertEquals(1, $content['meta']['total']);
    }

    public function testGetDetails(): void
    {
        $request = new Request(['locale' => 'en']);
        $rarity = $this->createRarityMock(1, 500, 50);

        $response = $this->controller->getDetails($rarity, $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testCreateSuccess(): void
    {
        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'minExperience' => 500,
            'experienceGain' => 50,
            'translations' => ['fr' => 'Légendaire', 'en' => 'Legendary']
        ]));

        $this->dtoValidator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(CreateRarityRequest::class));

        $this->entityManager->expects($this->exactly(3))->method('persist'); 
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->create($request, $this->entityManager, $this->dtoValidator);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testUpdateSuccess(): void
    {
        $rarity = clone $this->createRarityMock(1, 500, 50);
        $existingTranslation = new RarityTranslation();
        $existingTranslation->setLocale('fr')->setName('Ancien nom');
        $rarity->addRarityTranslation($existingTranslation);

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'minExperience' => 1000,
            'experienceGain' => 100,
            'translations' => [
                'fr' => 'Nouveau nom fr',
                'en' => 'New name en'
            ]
        ]));

        $this->dtoValidator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(UpdateRarityRequest::class));

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->update($rarity, $request, $this->entityManager, $this->dtoValidator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(1000, $rarity->getMinExperience());
        $this->assertEquals(100, $rarity->getExperienceGain());
        $this->assertEquals('Nouveau nom fr', $rarity->getTranslation('fr')->getName());
    }

    public function testDeleteSuccess(): void
    {
        $rarity = $this->createRarityMock(1, 100, 10);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($rarity);
        
        $this->entityManager->expects($this->once())
            ->method('flush');

        $response = $this->controller->delete($rarity, $this->entityManager);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}