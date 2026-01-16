<?php

namespace App\Controller;

use App\Dto\User\CreateUserRequest;
use App\Dto\User\UpdateUserPasswordRequest;
use App\Dto\User\UpdateUserRequest;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Validator\DtoValidator;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[OA\Parameter(
    name: 'locale',
    in: 'query',
    required: false,
    description: 'optional locale (ex: fr, en)',
    schema: new OA\Schema(type: 'string', example: 'fr')
)]
#[OA\Tag(name: 'user', description: 'User management endpoints')]
#[Route('/api/users', name: 'app_user_')]
final class UserController extends AbstractController
{
    #[OA\Get(
        summary: 'List users',
        description: 'Returns a list of users (Admin only).',
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Page number',
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'Items per page',
                schema: new OA\Schema(type: 'integer', default: 10, minimum: 5, maximum: 100)
            ),
            new OA\Parameter(
                name: 'q',
                in: 'query',
                description: 'Search term (firstname, lastname, email)',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                description: 'Column to sort by',
                schema: new OA\Schema(type: 'string', enum: ['id', 'firstname', 'lastname', 'email', 'company', 'roles'], default: 'id')
            ),
            new OA\Parameter(
                name: 'direction',
                in: 'query',
                description: 'Sort direction',
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'asc')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of users',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'firstname', type: 'string', example: 'Jean'),
                                    new OA\Property(property: 'lastname', type: 'string', example: 'Dupont'),
                                    new OA\Property(property: 'email', type: 'string', example: 'jean.dupont@example.com'),
                                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                                    new OA\Property(property: 'isVerified', type: 'boolean'),
                                ],
                                type: 'object'
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'page', type: 'integer', example: 1),
                                new OA\Property(property: 'limit', type: 'integer', example: 10),
                                new OA\Property(property: 'total', type: 'integer', example: 50),
                                new OA\Property(property: 'sort', type: 'string', example: 'id'),
                                new OA\Property(property: 'direction', type: 'string', example: 'asc'),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(UserRepository $userRepository, Request $request, PaginatorInterface $paginator): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = (int) max(5, min(100, $request->query->getInt('limit', 10)));

        $search = $request->query->get('q');
        $search = is_string($search) ? $search : null;

        $sort = (string) $request->query->get('sort', 'id');
        $direction = (string) $request->query->get('direction', 'asc');

        if ($search) {
            $sort = 'relevance';
            $direction = 'asc';
        }

        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        $queryBuilder = $userRepository->createSearchQueryBuilder($search, $sort, $direction, $currentUser?->getId());

        $pagination = $paginator->paginate(
            $queryBuilder,
            $page,
            $limit,
            [
                'sortFieldParameterName' => null,
                'sortDirectionParameterName' => null,
            ]
        );

        $usersData = array_map(fn (User $u) => $u->toArray(), iterator_to_array($pagination->getItems()));

        return new JsonResponse([
            'data' => $usersData,
            'meta' => [
                'page' => $pagination->getCurrentPageNumber(),
                'limit' => $pagination->getItemNumberPerPage(),
                'total' => $pagination->getTotalItemCount(),
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    #[OA\Get(
        summary: 'Get user details',
        description: 'Returns details of a specific user. Admin can access any user. Regular users can access themselves.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'firstname', type: 'string'),
                        new OA\Property(property: 'lastname', type: 'string'),
                        new OA\Property(property: 'company', type: 'string', nullable: true),
                        new OA\Property(property: 'email', type: 'string'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'isVerified', type: 'boolean'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 403, description: 'Access denied'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    #[Route('/{id}', name: 'details', methods: ['GET'])]
    public function getDetails(User $user): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser || (!$this->isGranted('ROLE_ADMIN') && $currentUser->getId() !== $user->getId())) {
            throw new ApiException(Response::HTTP_FORBIDDEN, 'Access denied');
        }

        return new JsonResponse($user->__serialize());
    }

    #[OA\Post(
        summary: 'Create a new user',
        description: 'Create a user (Admin only).',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'firstname', type: 'string', example: 'Jean'),
                    new OA\Property(property: 'lastname', type: 'string', example: 'Dupont'),
                    new OA\Property(property: 'email', type: 'string', example: 'jean.dupont@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'Secret123!'),
                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
                    new OA\Property(property: 'isVerified', type: 'boolean', example: false),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'firstname', type: 'string'),
                        new OA\Property(property: 'lastname', type: 'string'),
                        new OA\Property(property: 'company', type: 'string', nullable: true),
                        new OA\Property(property: 'email', type: 'string'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'isVerified', type: 'boolean'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        DtoValidator $dtoValidator,
        EmailVerifier $emailVerifier,
    ): JsonResponse {
        $data = json_decode((string) $request->getContent(), true);

        $dto = new CreateUserRequest(
            $data['firstname'] ?? '',
            $data['lastname'] ?? '',
            $data['company'] ?? null,
            $data['email'] ?? '',
            $data['password'] ?? '',
            $data['roles'] ?? ['ROLE_USER'],
            $data['isVerified'] ?? false
        );

        $dtoValidator->validate($dto);

        $user = new User();
        $user->setFirstname($dto->getFirstname())
            ->setLastname($dto->getLastname())
            ->setEmail($dto->getEmail())
            ->setCompany($dto->getCompany())
            ->setRoles(array_values($dto->getRoles()))
            ->setIsVerified($dto->isVerified())
            ->setPassword($passwordHasher->hashPassword($user, $dto->getPassword()));

        if (!$user->isVerified()) {
            $emailVerifier->sendEmailConfirmation('app_auth_verify_email', $user, $request->getLocale());
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse($user->__serialize(), Response::HTTP_CREATED);
    }

    #[OA\Put(
        summary: 'Update a user',
        description: 'Update user data. Admin can update any user. Regular users can update themselves.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'firstname', type: 'string', example: 'Jean'),
                    new OA\Property(property: 'lastname', type: 'string', example: 'Dupont'),
                    new OA\Property(property: 'email', type: 'string', example: 'jean.dupont@example.com'),
                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
                    new OA\Property(property: 'isVerified', type: 'boolean', example: false),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'firstname', type: 'string'),
                        new OA\Property(property: 'lastname', type: 'string'),
                        new OA\Property(property: 'company', type: 'string', nullable: true),
                        new OA\Property(property: 'email', type: 'string'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'isVerified', type: 'boolean'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 403, description: 'Access denied'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        DtoValidator $dtoValidator,
        EmailVerifier $emailVerifier,
        TranslatorInterface $translator,
    ): JsonResponse {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        if (!$currentUser || (!$isAdmin && $currentUser->getId() !== $user->getId())) {
            throw new ApiException(Response::HTTP_FORBIDDEN, 'Access denied');
        }

        $data = json_decode((string) $request->getContent(), true);

        $dto = new UpdateUserRequest(
            $data['firstname'] ?? '',
            $data['lastname'] ?? '',
            $data['company'] ?? null,
            $data['email'] ?? '',
            $this->isGranted('ROLE_ADMIN') ? $data['roles'] ?? [] : $currentUser->getRoles(),
            $this->isGranted('ROLE_ADMIN') ? $data['isVerified'] ?? false : $currentUser->isVerified()
        );

        $dtoValidator->validate($dto);

        $existingUser = $userRepository->findOneBy(['email' => $dto->getEmail()]);
        if ($existingUser && $existingUser->getId() !== $currentUser->getId()) {
            throw new ApiException(Response::HTTP_BAD_REQUEST, 'Validation failed', ['email' => [$translator->trans('user_already_exists', [], 'validators')]]);
        }

        $user->setFirstname($dto->getFirstname())
            ->setLastname($dto->getLastname())
            ->setEmail($dto->getEmail())
            ->setCompany($dto->getCompany());

        if ($isAdmin) {
            $user->setRoles(array_values($dto->getRoles()))
                ->setIsVerified($dto->getIsVerified());
        } elseif ($user->getEmail() !== $currentUser->getEmail()) {
            $user->setIsVerified(false);
        }

        if (!$user->isVerified()) {
            $emailVerifier->sendEmailConfirmation('app_auth_verify_email', $user, $request->getLocale());
        }

        $em->flush();

        $response = new JsonResponse($user->__serialize());
        if ($currentUser->getId() === $user->getId()) {
            $response->headers->clearCookie('REFRESH_TOKEN');
        }

        return $response;
    }

    #[OA\Put(
        summary: 'Update user password',
        description: 'Change password for the authenticated user.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'currentPassword', type: 'string', nullable: true, example: 'OldPass123'),
                    new OA\Property(property: 'newPassword', type: 'string', example: 'NewSecret123!'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 204, description: 'Password updated'),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 403, description: 'Access denied'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    #[Route('/{id}/password', name: 'update_password', methods: ['PUT'])]
    public function updatePassword(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        DtoValidator $dtoValidator,
    ): JsonResponse {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser || $currentUser->getId() !== $user->getId()) {
            throw new ApiException(Response::HTTP_FORBIDDEN, 'Access denied');
        }

        $data = json_decode((string) $request->getContent(), true);

        $dto = new UpdateUserPasswordRequest(
            $data['currentPassword'] ?? '',
            $data['newPassword'] ?? ''
        );

        $dtoValidator->validate($dto);

        $user->setPassword($passwordHasher->hashPassword($user, $dto->getNewPassword()));

        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[OA\Delete(
        summary: 'Delete a user',
        description: 'Delete a user. Admin can delete any user. Regular users can delete themselves.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'User deleted'),
            new OA\Response(response: 403, description: 'Access denied'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(User $user, EntityManagerInterface $em): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser || (!$this->isGranted('ROLE_ADMIN') && $currentUser->getId() !== $user->getId())) {
            throw new ApiException(Response::HTTP_FORBIDDEN, 'Access denied');
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
