<?php

namespace App\Tests\Unit\Controller;

use App\Controller\UserController;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Validator\DtoValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Repository\CompanyRepository;

class UserControllerTest extends TestCase
{
    private UserController $controller;
    private UserRepository&MockObject $userRepository;
    private CompanyRepository&MockObject $companyRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private DtoValidator&MockObject $dtoValidator;
    private EmailVerifier&MockObject $emailVerifier;
    private TranslatorInterface&MockObject $translator;
    private PaginatorInterface&MockObject $paginator;
    private TokenStorageInterface&MockObject $tokenStorage;
    private AuthorizationCheckerInterface&MockObject $authChecker;
    private ContainerInterface&MockObject $container;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->companyRepository = $this->createMock(CompanyRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dtoValidator = $this->createMock(DtoValidator::class);
        $this->emailVerifier = $this->createMock(EmailVerifier::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);

        $this->container->method('has')->willReturn(true);

        $this->container->method('get')->willReturnMap([
            ['security.token_storage', $this->tokenStorage],
            ['security.authorization_checker', $this->authChecker],
            ['parameter_bag', $this->createMock(ContainerBagInterface::class)],
        ]);

        $this->controller = new UserController();
        $this->controller->setContainer($this->container);
    }

    private function setId(User $user, int $id): User
    {
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, $id);

        return $user;
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

    public function testListUsers(): void
    {
        $this->configureSecurity(new User(), true);

        $request = new Request(['page' => 1, 'limit' => 10]);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->userRepository->expects($this->once())
            ->method('createSearchQueryBuilder')
            ->willReturn($queryBuilder);

        $pagination->method('getItems')->willReturn([
            (new User())->setFirstname('John')->setLastname('Doe')->setEmail('john@doe.com'),
        ]);
        $pagination->method('getCurrentPageNumber')->willReturn(1);
        $pagination->method('getItemNumberPerPage')->willReturn(10);
        $pagination->method('getTotalItemCount')->willReturn(1);

        $this->paginator->expects($this->once())
            ->method('paginate')
            ->willReturn($pagination);

        $response = $this->controller->list($this->userRepository, $request, $this->paginator);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testGetDetailsAccessDenied(): void
    {
        $currentUser = $this->setId(new User(), 1);
        $targetUser = $this->setId(new User(), 2);

        $this->configureSecurity($currentUser, false);

        try {
            $this->controller->getDetails($targetUser);
            $this->fail('ApiException expected');
        } catch (ApiException $e) {
            $this->assertEquals(Response::HTTP_FORBIDDEN, $e->getStatusCode());
        }
    }

    public function testGetDetailsSuccessAsAdmin(): void
    {
        $currentUser = $this->setId(new User(), 1);
        $targetUser = $this->setId(new User(), 2);
        $targetUser->setEmail('target@test.com');

        $this->configureSecurity($currentUser, true);

        $response = $this->controller->getDetails($targetUser);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testUpdateSelfCannotChangeRoles(): void
    {
        $user = $this->setId(new User(), 1);
        $user->setEmail('me@test.com')->setRoles(['ROLE_USER'])->setIsVerified(true);

        $this->configureSecurity($user, false);

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'firstname' => 'Updated',
            'lastname' => 'Me',
            'roles' => ['ROLE_ADMIN'],
            'email' => 'me@test.com',
        ]));

        $this->dtoValidator->expects($this->once())->method('validate');
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->update(
            $user,
            $request,
            $this->entityManager,
            $this->userRepository,
            $this->companyRepository,
            $this->dtoValidator,
            $this->emailVerifier,
            $this->translator
        );

        $this->assertEquals('Updated', $user->getFirstname());
        $this->assertNotContains('ROLE_ADMIN', $user->getRoles());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testUpdateAsAdminCanChangeRoles(): void
    {
        $admin = $this->setId(new User(), 1);
        $targetUser = $this->setId(new User(), 2);
        $targetUser->setRoles(['ROLE_USER']);

        $this->configureSecurity($admin, true);

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'firstname' => 'User',
            'lastname' => 'Name',
            'email' => 'user@test.com',
            'roles' => ['ROLE_ADMIN'],
        ]));

        $this->controller->update(
            $targetUser,
            $request,
            $this->entityManager,
            $this->userRepository,
            $this->companyRepository,
            $this->dtoValidator,
            $this->emailVerifier,
            $this->translator
        );

        $this->assertContains('ROLE_ADMIN', $targetUser->getRoles());
    }

    public function testUpdateEmailTriggerVerification(): void
    {
        $user = $this->setId(new User(), 1);
        $user->setEmail('old@test.com')->setIsVerified(true);

        $currentUserState = clone $user;

        $this->configureSecurity($currentUserState, false);

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'firstname' => 'User',
            'lastname' => 'Name',
            'email' => 'new@test.com',
        ]));

        $this->emailVerifier->expects($this->once())
            ->method('sendEmailConfirmation');

        $this->controller->update(
            $user,
            $request,
            $this->entityManager,
            $this->userRepository,
            $this->companyRepository,
            $this->dtoValidator,
            $this->emailVerifier,
            $this->translator
        );

        $this->assertEquals('new@test.com', $user->getEmail());
        $this->assertFalse($user->isVerified());
    }

    public function testUpdateEmailCollision(): void
    {
        $user = $this->setId(new User(), 1);
        $user->setEmail('me@test.com')->setIsVerified(true);

        $otherUser = $this->setId(new User(), 2);
        $otherUser->setEmail('taken@test.com');

        $this->configureSecurity($user, false);

        $request = new Request([], [], [], [], [], [], (string) json_encode([
            'firstname' => 'Me',
            'lastname' => 'Me',
            'email' => 'taken@test.com',
        ]));

        $this->userRepository->method('findOneBy')->willReturn($otherUser);
        $this->translator->method('trans')->willReturn('Email taken');

        try {
            $this->controller->update(
                $user,
                $request,
                $this->entityManager,
                $this->userRepository,
                $this->companyRepository,
                $this->dtoValidator,
                $this->emailVerifier,
                $this->translator
            );
            $this->fail('ApiException expected');
        } catch (ApiException $e) {
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $e->getStatusCode());
        }
    }

    public function testDeleteAccessDenied(): void
    {
        $currentUser = $this->setId(new User(), 1);
        $targetUser = $this->setId(new User(), 2);

        $this->configureSecurity($currentUser, false);

        try {
            $this->controller->delete($targetUser, $this->entityManager);
            $this->fail('ApiException expected');
        } catch (ApiException $e) {
            $this->assertEquals(Response::HTTP_FORBIDDEN, $e->getStatusCode());
        }
    }

    public function testDeleteSuccess(): void
    {
        $currentUser = $this->setId(new User(), 1);

        $this->configureSecurity($currentUser, false);

        $this->entityManager->expects($this->once())->method('remove')->with($currentUser);
        $this->entityManager->expects($this->once())->method('flush');

        $response = $this->controller->delete($currentUser, $this->entityManager);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
