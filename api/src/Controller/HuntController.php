<?php

namespace App\Controller;

use App\Dto\Hunt\CreateHuntRequest;
use App\Dto\Hunt\UpdateHuntRequest;
use App\Dto\Reward\CreateRewardRequest;
use App\Entity\Hunt;
use App\Entity\HuntTranslation;
use App\Entity\Reward;
use App\Entity\RewardTranslation;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\CategoryRepository;
use App\Repository\HuntRepository;
use App\Repository\RarityRepository;
use App\Validator\DtoValidator;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Parameter(name: 'locale', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'fr'))]
#[OA\Tag(name: 'hunt', description: 'Hunt management endpoints')]
#[Route('/api/hunts', name: 'app_hunt_')]
final class HuntController extends AbstractController
{
    private function canManageHunt(Hunt $hunt): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if ($currentUser && $currentUser->getCompany() && $hunt->getCompany() === $currentUser->getCompany()) {
            return true;
        }

        return false;
    }

    #[OA\Get(
        summary: 'List all hunts',
        description: 'Returns a full list of all hunts without pagination.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of all hunts',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'public_list', methods: ['GET'])]
    public function listPublic(HuntRepository $huntRepository, Request $request): JsonResponse
    {
        $locale = is_string($request->query->get('locale')) ? (string) $request->query->get('locale') : null;
        $hunts = $huntRepository->findAll();

        $data = array_map(fn (Hunt $h) => $h->toArray($locale), $hunts);

        return new JsonResponse(['data' => $data]);
    }

    #[OA\Get(
        summary: 'List hunts (Admin/User)',
        description: 'Admins see all hunts, users see only those of their company.',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'q', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', default: 'id')),
            new OA\Parameter(name: 'direction', in: 'query', schema: new OA\Schema(type: 'string', default: 'desc')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of hunts',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'page', type: 'integer'),
                                new OA\Property(property: 'limit', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
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
    #[IsGranted('ROLE_USER')]
    #[Route('/admin', name: 'admin_list', methods: ['GET'])]
    public function listAdmin(HuntRepository $huntRepository, Request $request, PaginatorInterface $paginator): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = (int) max(5, min(100, $request->query->getInt('limit', 10)));
        $search = is_string($request->query->get('q')) ? (string) $request->query->get('q') : null;
        $sort = (string) $request->query->get('sort', 'id');
        $direction = (string) $request->query->get('direction', 'desc');
        $locale = is_string($request->query->get('locale')) ? (string) $request->query->get('locale') : null;

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $companyId = $currentUser->getCompany()?->getId();

        $queryBuilder = $huntRepository->createSearchQueryBuilder($search, $sort, $direction, $companyId, $isAdmin);

        $pagination = $paginator->paginate($queryBuilder, $page, $limit, ['distinct' => true]);
        $huntsData = array_map(fn (Hunt $h) => $h->toArray($locale), iterator_to_array($pagination->getItems()));

        return new JsonResponse([
            'data' => $huntsData,
            'meta' => [
                'page' => $pagination->getCurrentPageNumber(),
                'limit' => $pagination->getItemNumberPerPage(),
                'total' => $pagination->getTotalItemCount(),
            ],
        ]);
    }

    #[OA\Get(
        summary: 'Get hunt details',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Hunt details'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Hunt not found'),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'details', methods: ['GET'])]
    public function getDetails(Hunt $hunt, Request $request): JsonResponse
    {
        $locale = is_string($request->query->get('locale')) ? (string) $request->query->get('locale') : null;

        return new JsonResponse($hunt->toArray($locale));
    }

    #[OA\Post(
        summary: 'Create a new hunt with its mandatory reward',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'lat', type: 'number', format: 'float', example: 48.8566),
                    new OA\Property(property: 'lon', type: 'number', format: 'float', example: 2.3522),
                    new OA\Property(property: 'categoryId', type: 'integer', example: 1),
                    new OA\Property(property: 'rarityId', type: 'integer', example: 2),
                    new OA\Property(
                        property: 'translations',
                        properties: [
                            new OA\Property(
                                property: 'fr',
                                properties: [
                                    new OA\Property(property: 'title', type: 'string', example: 'Le Trésor Caché'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Trouvez le coffre.'),
                                    new OA\Property(property: 'question', type: 'string', example: 'Quelle est la couleur du cheval blanc ?'),
                                    new OA\Property(property: 'answer', type: 'string', example: 'Blanc'),
                                    new OA\Property(property: 'location', type: 'string', example: 'Paris'),
                                ],
                                type: 'object'
                            ),
                            new OA\Property(
                                property: 'en',
                                properties: [
                                    new OA\Property(property: 'title', type: 'string', example: 'Hidden Treasure'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Find the chest.'),
                                    new OA\Property(property: 'question', type: 'string', example: 'What color is the white horse?'),
                                    new OA\Property(property: 'answer', type: 'string', example: 'White'),
                                    new OA\Property(property: 'location', type: 'string', example: 'Paris'),
                                ],
                                type: 'object'
                            ),
                        ],
                        type: 'object'
                    ),
                    new OA\Property(
                        property: 'reward',
                        properties: [
                            new OA\Property(property: 'code', type: 'string', example: 'PROMO2026'),
                            new OA\Property(property: 'link', type: 'string', example: 'https://example.com/promo'),
                            new OA\Property(property: 'endDate', type: 'string', format: 'date-time'),
                            new OA\Property(
                                property: 'translations',
                                properties: [
                                    new OA\Property(property: 'fr', type: 'string', example: 'Récompense Épique'),
                                    new OA\Property(property: 'en', type: 'string', example: 'Epic Reward'),
                                ],
                                type: 'object'
                            ),
                        ],
                        type: 'object'
                    ),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Hunt and Reward created'),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        DtoValidator $dtoValidator,
        CategoryRepository $categoryRepository,
        RarityRepository $rarityRepository,
    ): JsonResponse {
        $data = json_decode((string) $request->getContent(), true) ?? [];

        $huntDto = new CreateHuntRequest(
            $data['lat'] ?? 0.0,
            $data['lon'] ?? 0.0,
            $data['categoryId'] ?? null,
            $data['rarityId'] ?? 0,
            $data['translations'] ?? [],
            $data['reward'] ?? []
        );

        $dtoValidator->validate($huntDto);

        $rewardData = $huntDto->getReward();
        $rewardDto = new CreateRewardRequest(
            $rewardData['code'] ?? '',
            $rewardData['link'] ?? '',
            $rewardData['endDate'] ?? '',
            $rewardData['translations'] ?? []
        );

        $dtoValidator->validate($rewardDto);

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser->getCompany()) {
            throw new ApiException(Response::HTTP_BAD_REQUEST, 'User must belong to a company to create a hunt');
        }

        $rarity = $rarityRepository->find($huntDto->getRarityId());
        if (!$rarity) {
            throw new ApiException(Response::HTTP_BAD_REQUEST, 'Validation failed', ['rarityId' => ['Rarity not found']]);
        }

        $category = null;
        if ($huntDto->getCategoryId()) {
            $category = $categoryRepository->find($huntDto->getCategoryId());
            if (!$category) {
                throw new ApiException(Response::HTTP_BAD_REQUEST, 'Validation failed', ['categoryId' => ['Category not found']]);
            }
        }

        $hunt = new Hunt();
        $hunt->setLat($huntDto->getLat())
             ->setLon($huntDto->getLon())
             ->setCompany($currentUser->getCompany())
             ->setRarity($rarity)
             ->setCategory($category);

        foreach ($huntDto->getTranslations() as $locale => $tData) {
            $translation = new HuntTranslation();
            $translation->setLocale((string) $locale)
                ->setTitle($tData['title'])
                ->setDescription($tData['description'])
                ->setQuestion($tData['question'])
                ->setAnswer($tData['answer'])
                ->setLocation($tData['location']);
            $hunt->addHuntTranslation($translation);
            $em->persist($translation);
        }

        $reward = new Reward();
        $reward->setCode($rewardDto->getCode())
               ->setLink($rewardDto->getLink())
               ->setEndDate($rewardDto->getEndDate())
               ->setHunt($hunt);

        foreach ($rewardDto->getTranslations() as $locale => $title) {
            $rTranslation = new RewardTranslation();
            $rTranslation->setLocale((string) $locale)->setTitle($title);
            $reward->addRewardTranslation($rTranslation);
            $em->persist($rTranslation);
        }

        $em->persist($hunt);
        $em->flush();

        return new JsonResponse($hunt->toArray(), Response::HTTP_CREATED);
    }

    #[OA\Put(
        summary: 'Update a hunt',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'lat', type: 'number', format: 'float', example: 48.8566),
                    new OA\Property(property: 'lon', type: 'number', format: 'float', example: 2.3522),
                    new OA\Property(property: 'categoryId', type: 'integer', example: 1),
                    new OA\Property(property: 'rarityId', type: 'integer', example: 2),
                    new OA\Property(
                        property: 'translations',
                        properties: [
                            new OA\Property(
                                property: 'fr',
                                properties: [
                                    new OA\Property(property: 'title', type: 'string', example: 'Le Trésor Caché (Modifié)'),
                                    new OA\Property(property: 'description', type: 'string'),
                                    new OA\Property(property: 'question', type: 'string'),
                                    new OA\Property(property: 'answer', type: 'string'),
                                    new OA\Property(property: 'location', type: 'string'),
                                ],
                                type: 'object'
                            ),
                            new OA\Property(
                                property: 'en',
                                properties: [
                                    new OA\Property(property: 'title', type: 'string', example: 'Hidden Treasure (Edited)'),
                                    new OA\Property(property: 'description', type: 'string'),
                                    new OA\Property(property: 'question', type: 'string'),
                                    new OA\Property(property: 'answer', type: 'string'),
                                    new OA\Property(property: 'location', type: 'string'),
                                ],
                                type: 'object'
                            ),
                        ],
                        type: 'object'
                    ),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Hunt updated'),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Hunt not found'),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        Hunt $hunt,
        Request $request,
        EntityManagerInterface $em,
        DtoValidator $dtoValidator,
        CategoryRepository $categoryRepository,
        RarityRepository $rarityRepository,
    ): JsonResponse {
        if (!$this->canManageHunt($hunt)) {
            throw new ApiException(Response::HTTP_FORBIDDEN, 'You cannot edit this hunt');
        }

        $data = json_decode((string) $request->getContent(), true) ?? [];

        $dto = new UpdateHuntRequest(
            $data['lat'] ?? $hunt->getLat(),
            $data['lon'] ?? $hunt->getLon(),
            array_key_exists('categoryId', $data) ? $data['categoryId'] : $hunt->getCategory()?->getId(),
            $data['rarityId'] ?? $hunt->getRarity()?->getId(),
            $data['translations'] ?? []
        );

        $dtoValidator->validate($dto);

        $rarity = $rarityRepository->find($dto->getRarityId());
        if (!$rarity) {
            throw new ApiException(Response::HTTP_BAD_REQUEST, 'Validation failed', ['rarityId' => ['Rarity not found']]);
        }

        $category = null;
        if ($dto->getCategoryId()) {
            $category = $categoryRepository->find($dto->getCategoryId());
            if (!$category) {
                throw new ApiException(Response::HTTP_BAD_REQUEST, 'Validation failed', ['categoryId' => ['Category not found']]);
            }
        }

        $hunt->setLat($dto->getLat())
             ->setLon($dto->getLon())
             ->setRarity($rarity)
             ->setCategory($category);

        foreach ($dto->getTranslations() as $locale => $tData) {
            $translation = $hunt->getTranslation($locale);
            if (!$translation) {
                $translation = new HuntTranslation();
                $translation->setLocale((string) $locale);
                $hunt->addHuntTranslation($translation);
                $em->persist($translation);
            }
            $translation->setTitle($tData['title'])
                        ->setDescription($tData['description'])
                        ->setQuestion($tData['question'])
                        ->setAnswer($tData['answer'])
                        ->setLocation($tData['location']);
        }

        $em->flush();

        return new JsonResponse($hunt->toArray());
    }

    #[OA\Delete(
        summary: 'Delete a hunt (and its reward cascade)',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Hunt and Reward deleted'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Hunt not found'),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Hunt $hunt, EntityManagerInterface $em): JsonResponse
    {
        if (!$this->canManageHunt($hunt)) {
            throw new ApiException(Response::HTTP_FORBIDDEN, 'You cannot delete this hunt');
        }

        $em->remove($hunt);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
