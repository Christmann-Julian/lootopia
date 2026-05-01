<?php

namespace App\Tests\Unit\Controller;

use App\Controller\StatisticsController;
use App\Entity\Company;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\CompanyRepository;
use App\Repository\HuntRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class StatisticsControllerTest extends TestCase
{
    private StatisticsController $controller;
    private UserRepository&MockObject $userRepository;
    private HuntRepository&MockObject $huntRepository;
    private CompanyRepository&MockObject $companyRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private ContainerInterface&MockObject $container;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->huntRepository = $this->createMock(HuntRepository::class);
        $this->companyRepository = $this->createMock(CompanyRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->container = $this->createMock(ContainerInterface::class);

        $this->controller = new StatisticsController();
        $this->controller->setContainer($this->container);
    }

    private function mockUser(?Company $company = null): void
    {
        $user = new User();
        if ($company) {
            $user->setCompany($company);
        }

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $this->container->method('has')->willReturnMap([
            ['security.token_storage', true],
        ]);
        $this->container->method('get')->willReturnMap([
            ['security.token_storage', $tokenStorage],
        ]);
    }

    public function testAdminStats(): void
    {
        $this->userRepository->expects($this->once())->method('count')->willReturn(150);
        $this->huntRepository->expects($this->once())->method('count')->willReturn(45);
        $this->companyRepository->expects($this->once())->method('count')->willReturn(10);

        $response = $this->controller->adminStats($this->userRepository, $this->huntRepository, $this->companyRepository);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);

        $this->assertEquals(150, $content['totalUsers']);
        $this->assertEquals(45, $content['totalHunts']);
        $this->assertEquals(10, $content['totalCompanies']);
    }

    public function testCompanyStatsSuccess(): void
    {
        $company = new Company();
        $this->mockUser($company);

        $this->huntRepository->expects($this->once())
            ->method('count')
            ->with(['company' => $company])
            ->willReturn(5);

        $queryMock1 = $this->createMock(AbstractQuery::class);
        $queryMock1->method('setParameter')->willReturnSelf();
        $queryMock1->method('getSingleScalarResult')->willReturn(25);

        $queryMock2 = $this->createMock(AbstractQuery::class);
        $queryMock2->method('setParameter')->willReturnSelf();
        $queryMock2->method('getSingleScalarResult')->willReturn(12);

        $this->entityManager->expects($this->exactly(2))
            ->method('createQuery')
            ->willReturnOnConsecutiveCalls($queryMock1, $queryMock2);

        $response = $this->controller->companyStats($this->huntRepository, $this->entityManager);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);

        $this->assertEquals(5, $content['totalHuntsCreated']);
        $this->assertEquals(12, $content['totalUniqueParticipants']);
        $this->assertEquals(25, $content['totalRewardsClaimed']);
    }

    public function testCompanyStatsWithoutCompanyThrowsException(): void
    {
        $this->mockUser(null);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(Response::HTTP_FORBIDDEN);

        $this->controller->companyStats($this->huntRepository, $this->entityManager);
    }
}
