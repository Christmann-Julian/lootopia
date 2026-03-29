<?php

namespace App\Controller;

use App\Dto\Rarity\CreateRarityRequest;
use App\Dto\Rarity\UpdateRarityRequest;
use App\Entity\Rarity;
use App\Entity\RarityTranslation;
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

#[OA\Parameter(
    name: 'locale',
    in: 'query',
    required: false,
    description: 'Optional locale (ex: fr, en) to get specific translation. If empty, returns all translations.',
    schema: new OA\Schema(type: 'string', example: 'fr')
)]
#[OA\Tag(name: 'rarity', description: 'Rarity management endpoints')]
#[Route('/api/rarities', name: 'app_rarity_')]
final class RarityController extends AbstractController
{
    #[OA\Get(
        summary: 'List rarities',
        description: 'Returns a list of all rarities.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of rarities',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'object'))
            ),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(RarityRepository $rarityRepository, Request $request): JsonResponse
    {
        $locale = (string) $request->query->get('locale');
        $rarities = $rarityRepository->findBy([], ['minExperience' => 'ASC']);

        $data = array_map(fn (Rarity $r) => $r->toArray($locale), $rarities);

        return new JsonResponse(['data' => $data]);
    }

    #[OA\Get(
        summary: 'List rarities (Admin)',
        description: 'Returns a paginated list of rarities for administration.',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)),
            new OA\Parameter(name: 'limit', in: 'query', description: 'Items per page', schema: new OA\Schema(type: 'integer', default: 10, minimum: 5, maximum: 100)),
            new OA\Parameter(name: 'q', in: 'query', description: 'Search term (translation name)', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', description: 'Column to sort by', schema: new OA\Schema(type: 'string', enum: ['id', 'minExperience', 'name'], default: 'minExperience')),
            new OA\Parameter(name: 'direction', in: 'query', description: 'Sort direction', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'asc')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of rarities',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin_list', methods: ['GET'])]
    public function adminList(RarityRepository $rarityRepository, Request $request, PaginatorInterface $paginator): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = (int) max(5, min(100, $request->query->getInt('limit', 10)));

        $search = $request->query->get('q');
        $search = is_string($search) ? $search : null;

        $sort = (string) $request->query->get('sort', 'minExperience');
        $direction = (string) $request->query->get('direction', 'asc');
        $locale = (string) $request->query->get('locale');

        if ($search) {
            $sort = 'relevance';
            $direction = 'asc';
        }

        $queryBuilder = $rarityRepository->createSearchQueryBuilder($search, $sort, $direction);

        $pagination = $paginator->paginate(
            $queryBuilder,
            $page,
            $limit,
            [
                'sortFieldParameterName' => null,
                'sortDirectionParameterName' => null,
                'distinct' => true,
            ]
        );

        $raritiesData = array_map(fn (Rarity $r) => $r->toArray($locale), iterator_to_array($pagination->getItems()));

        return new JsonResponse([
            'data' => $raritiesData,
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
        summary: 'Get rarity details',
        description: 'Returns details of a specific rarity.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Rarity details'),
            new OA\Response(response: 404, description: 'Rarity not found'),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'details', methods: ['GET'])]
    public function getDetails(Rarity $rarity, Request $request): JsonResponse
    {
        $locale = (string) $request->query->get('locale');

        return new JsonResponse($rarity->toArray($locale));
    }

    #[OA\Post(
        summary: 'Create a new rarity',
        description: 'Create a rarity (Admin only).',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'minExperience', type: 'integer', example: 500),
                    new OA\Property(
                        property: 'experienceGain',
                        type: 'integer',
                        example: 50,
                    ),
                    new OA\Property(
                        property: 'translations',
                        properties: [
                            new OA\Property(property: 'fr', type: 'string', example: 'Légendaire'),
                            new OA\Property(property: 'en', type: 'string', example: 'Legendary'),
                        ],
                        type: 'object'
                    ),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Rarity created'),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        DtoValidator $dtoValidator,
    ): JsonResponse {
        $data = json_decode((string) $request->getContent(), true) ?? [];

        $dto = new CreateRarityRequest(
            $data['minExperience'] ?? 0,
            $data['experienceGain'] ?? 0,
            $data['translations'] ?? []
        );

        $dtoValidator->validate($dto);

        $rarity = new Rarity();
        $rarity->setMinExperience($dto->getMinExperience());
        $rarity->setExperienceGain($dto->getExperienceGain());

        foreach ($dto->getTranslations() as $locale => $name) {
            $translation = new RarityTranslation();
            $translation->setLocale((string) $locale)
                ->setName($name);
            $rarity->addRarityTranslation($translation);
            $em->persist($translation);
        }

        $em->persist($rarity);
        $em->flush();

        return new JsonResponse($rarity->toArray(), Response::HTTP_CREATED);
    }

    #[OA\Put(
        summary: 'Update a rarity',
        description: 'Update rarity data (Admin only).',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'minExperience', type: 'integer', example: 1000),
                    new OA\Property(
                        property: 'experienceGain',
                        type: 'integer',
                        example: 100,
                    ),
                    new OA\Property(
                        property: 'translations',
                        properties: [
                            new OA\Property(property: 'fr', type: 'string', example: 'Mythique'),
                            new OA\Property(property: 'en', type: 'string', example: 'Mythic'),
                        ],
                        type: 'object'
                    ),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Rarity updated'),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Rarity not found'),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        Rarity $rarity,
        Request $request,
        EntityManagerInterface $em,
        DtoValidator $dtoValidator,
    ): JsonResponse {
        $data = json_decode((string) $request->getContent(), true) ?? [];

        $dto = new UpdateRarityRequest(
            $data['minExperience'] ?? $rarity->getMinExperience(),
            $data['experienceGain'] ?? $rarity->getExperienceGain(),
            $data['translations'] ?? []
        );

        $dtoValidator->validate($dto);

        $rarity->setMinExperience($dto->getMinExperience());
        $rarity->setExperienceGain($dto->getExperienceGain());

        foreach ($dto->getTranslations() as $locale => $name) {
            $translation = $rarity->getTranslation((string) $locale);

            if ($translation) {
                $translation->setName($name);
            } else {
                $translation = new RarityTranslation();
                $translation->setLocale((string) $locale)
                    ->setName($name);
                $rarity->addRarityTranslation($translation);
                $em->persist($translation);
            }
        }

        $em->flush();

        return new JsonResponse($rarity->toArray());
    }

    #[OA\Delete(
        summary: 'Delete a rarity',
        description: 'Delete a rarity and its translations (Admin only).',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Rarity deleted'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Rarity not found'),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Rarity $rarity, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($rarity);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
