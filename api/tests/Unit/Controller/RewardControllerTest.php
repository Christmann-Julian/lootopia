<?php

namespace App\Tests\Unit\Controller;

use App\Controller\RewardController;
use App\Dto\Reward\UpdateRewardRequest;
use App\Entity\Company;
use App\Entity\Hunt;
use App\Entity\Reward;
use App\Entity\RewardTranslation;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\RewardRepository;
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

class RewardControllerTest extends TestCase
{
    private RewardController $controller;
    private RewardRepository&MockObject $rewardRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private DtoValidator&MockObject $dtoValidator;
    private PaginatorInterface&MockObject $paginator;
    private TokenStorageInterface&MockObject $tokenStorage;
    private AuthorizationCheckerInterface&MockObject $authChecker;
    private ContainerInterface&MockObject $container;

    protected function setUp(): void
    {
        $this->rewardRepository = $this->createMock(RewardRepository::class);
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

        $this->controller = new RewardController();
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

    private function createRewardMock(int $id, string $code): Reward
    {
        $reward = new Reward();
        $reward->setCode($code);

        $reflection = new \ReflectionClass($reward);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($reward, $id);

        return $reward;
    }

    public function testList(): void
    {
        $user = new User();
        $company = new Company();
        $user->setCompany($company);

        $this->configureSecurity($user, false);

        $request = new Request(['page' => 1, 'limit' => 10]);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->rewardRepository->expects($this->once())
            ->method('createSearchQueryBuilder')
            ->willReturn($queryBuilder);

        $pagination->method('getItems')->willReturn([
            $this->createRewardMock(1, 'PROMO1'),
        ]);
        $pagination->method('getCurrentPageNumber')->willReturn(1);
        $pagination->method('getItemNumberPerPage')->willReturn(10);
        $pagination->method('getTotalItemCount')->willReturn(1);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->willReturn($pagination);

        $response = $this->controller->list($this->rewardRepository, $request, $this->paginator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);
        $this->assertArrayHasKey('meta', $content);
    }

    public function testGetDetails(): void
    {
        $request = new Request(['locale' => 'en']);
        $reward = $this->createRewardMock(1, 'PROMO1');

        $response = $this->controller->getDetails($reward, $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testUpdateForbidden(): void
    {
        $user = new User();
        $this->configureSecurity($user, false);

        $reward = $this->createRewardMock(1, 'PROMO1');
        $request = new Request([], [], [], [], [], [], (string) json_encode(['code' => 'NEWPROMO']));

        try {
            $this->controller->update($reward, $request, $this->entityManager, $this->dtoValidator);
            $this->fail('ApiException expected');
        } catch (ApiException $e) {
            $this->assertEquals(Response::HTTP_FORBIDDEN, $e->getStatusCode());
            $this->assertEquals('You cannot edit this reward', $e->getMessage());
        }
    }

    public function testUpdateSuccessAsAdmin(): void
    {
        $admin = new User();
        $this->configureSecurity($admin, true);

        $reward = clone $this->createRewardMock(1, 'OLDPROMO');
        $existingTranslation = new RewardTranslation();
        $existingTranslation->setLocale('fr')->setTitle('Ancien titre');
        $reward->addRewardTranslation($existingTranslation);

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'code' => 'NEWPROMO',
            'translations' => [
                'fr' => 'Nouveau titre fr',
                'en' => 'New title en',
            ],
        ]));

        $this->dtoValidator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(UpdateRewardRequest::class));

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->update($reward, $request, $this->entityManager, $this->dtoValidator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('NEWPROMO', $reward->getCode());
        $this->assertEquals('Nouveau titre fr', $reward->getTranslation('fr')?->getTitle());
    }

    public function testUpdateSuccessAsCompanyOwner(): void
    {
        $company = new Company();

        $user = new User();
        $user->setCompany($company);

        $hunt = new Hunt();
        $hunt->setCompany($company);

        $reward = $this->createRewardMock(1, 'PROMO1');
        $reward->setHunt($hunt);

        $this->configureSecurity($user, false);

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'code' => 'UPDATED',
            'link' => 'https://example.com/updated',
            'endDate' => (new \DateTime('+1 day'))->format('Y-m-d\TH:i:sP'),
            'translations' => [
                'fr' => 'Titre mis à jour',
                'en' => 'Updated title',
            ],
        ]));

        $this->dtoValidator->expects($this->once())->method('validate');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->update($reward, $request, $this->entityManager, $this->dtoValidator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('UPDATED', $reward->getCode());
    }
}
