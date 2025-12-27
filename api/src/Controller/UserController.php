<?php

namespace App\Controller;

use App\Dto\User\CreateUserRequest;
use App\Dto\User\UpdateUserPasswordRequest;
use App\Dto\User\UpdateUserRequest;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\UserRepository;
use App\Validator\DtoValidator;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of users',
                content: new OA\JsonContent(
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
                )
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        $data = array_map(fn (User $u) => $u->__serialize(), $users);

        return new JsonResponse($data);
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
        DtoValidator $dtoValidator,
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

        $em->flush();

        return new JsonResponse($user->__serialize());
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
}
