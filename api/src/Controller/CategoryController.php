<?php

namespace App\Controller;

use App\Dto\Category\CreateCategoryRequest;
use App\Dto\Category\UpdateCategoryRequest;
use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Repository\CategoryRepository;
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
#[OA\Tag(name: 'category', description: 'Category management endpoints')]
#[Route('/api/categories', name: 'app_category_')]
final class CategoryController extends AbstractController
{
    #[OA\Get(
        summary: 'List categories (Admin)',
        description: 'Returns a paginated list of categories for administration.',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)),
            new OA\Parameter(name: 'limit', in: 'query', description: 'Items per page', schema: new OA\Schema(type: 'integer', default: 10, minimum: 5, maximum: 100)),
            new OA\Parameter(name: 'q', in: 'query', description: 'Search term (icon, translation name)', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', description: 'Column to sort by', schema: new OA\Schema(type: 'string', enum: ['id', 'icon', 'name'], default: 'id')),
            new OA\Parameter(name: 'direction', in: 'query', description: 'Sort direction', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'asc')),
            new OA\Parameter(name: 'locale', in: 'query', description: 'Locale for translations formatting', schema: new OA\Schema(type: 'string', example: 'fr')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of categories',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'icon', type: 'string'),
                                    new OA\Property(
                                        property: 'name',
                                        type: 'string',
                                        description: 'Translation name for the requested locale. Null if "locale" is omitted or translation is missing.',
                                        nullable: true
                                    ),
                                    new OA\Property(
                                        property: 'translations',
                                        type: 'object',
                                        description: 'Dictionary of translations (locale => name). Present if "locale" is omitted.',
                                        properties: [
                                            new OA\Property(property: 'fr', type: 'string'),
                                            new OA\Property(property: 'en', type: 'string'),
                                        ]
                                    ),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'page', type: 'integer'),
                                new OA\Property(property: 'limit', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                                new OA\Property(property: 'sort', type: 'string'),
                                new OA\Property(property: 'direction', type: 'string'),
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
    #[Route('/admin', name: 'admin_list', methods: ['GET'])]
    public function adminList(CategoryRepository $categoryRepository, Request $request, PaginatorInterface $paginator): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = (int) max(5, min(100, $request->query->getInt('limit', 10)));

        $search = $request->query->get('q');
        $search = is_string($search) ? $search : null;

        $sort = (string) $request->query->get('sort', 'id');
        $direction = (string) $request->query->get('direction', 'asc');
        $locale = (string) $request->query->get('locale');

        if ($search) {
            $sort = 'relevance';
            $direction = 'asc';
        }

        $queryBuilder = $categoryRepository->createSearchQueryBuilder($search, $sort, $direction);

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

        $categoriesData = array_map(fn (Category $c) => $c->toArray($locale), iterator_to_array($pagination->getItems()));

        return new JsonResponse([
            'data' => $categoriesData,
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
        summary: 'List categories',
        description: 'Returns a list of all categories.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of categories',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'icon', type: 'string'),
                                    new OA\Property(
                                        property: 'name',
                                        type: 'string',
                                        description: 'Translation name for the requested locale. Null if "locale" is omitted or translation is missing.',
                                        nullable: true
                                    ),
                                    new OA\Property(
                                        property: 'translations',
                                        type: 'object',
                                        description: 'Dictionary of translations (locale => name). Present if "locale" is omitted.',
                                        properties: [
                                            new OA\Property(property: 'fr', type: 'string'),
                                            new OA\Property(property: 'en', type: 'string'),
                                        ]
                                    ),
                                ]
                            )
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(CategoryRepository $categoryRepository, Request $request): JsonResponse
    {
        $locale = (string) $request->query->get('locale');
        $categories = $categoryRepository->findAll();

        $data = array_map(fn (Category $c) => $c->toArray($locale), $categories);

        return new JsonResponse(['data' => $data]);
    }

    #[OA\Get(
        summary: 'Get category details',
        description: 'Returns details of a specific category.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'icon', type: 'string'),
                        new OA\Property(
                            property: 'name',
                            type: 'string',
                            description: 'Translation name for the requested locale. Null if "locale" is omitted or translation is missing.',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'translations',
                            type: 'object',
                            description: 'Dictionary of translations (locale => name). Present if "locale" is omitted.',
                            properties: [
                                new OA\Property(property: 'fr', type: 'string'),
                                new OA\Property(property: 'en', type: 'string'),
                            ]
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'details', methods: ['GET'])]
    public function getDetails(Category $category, Request $request): JsonResponse
    {
        $locale = (string) $request->query->get('locale');

        return new JsonResponse($category->toArray($locale));
    }

    #[OA\Post(
        summary: 'Create a new category',
        description: 'Create a category (Admin only).',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'icon', type: 'string', example: 'fa-sword'),
                    new OA\Property(
                        property: 'translations',
                        properties: [
                            new OA\Property(property: 'fr', type: 'string', example: 'Épée'),
                            new OA\Property(property: 'en', type: 'string', example: 'Sword'),
                        ],
                        type: 'object'
                    ),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Category created'),
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

        $dto = new CreateCategoryRequest(
            $data['icon'] ?? '',
            $data['translations'] ?? []
        );

        $dtoValidator->validate($dto);

        $category = new Category();
        $category->setIcon($dto->getIcon());

        foreach ($dto->getTranslations() as $locale => $name) {
            $translation = new CategoryTranslation();
            $translation->setLocale((string) $locale)
                ->setName($name);
            $category->addCategoryTranslation($translation);
            $em->persist($translation);
        }

        $em->persist($category);
        $em->flush();

        return new JsonResponse($category->toArray(), Response::HTTP_CREATED);
    }

    #[OA\Put(
        summary: 'Update a category',
        description: 'Update category data (Admin only).',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'icon', type: 'string', example: 'fa-shield'),
                    new OA\Property(
                        property: 'translations',
                        properties: [
                            new OA\Property(property: 'fr', type: 'string', example: 'Bouclier'),
                            new OA\Property(property: 'en', type: 'string', example: 'Shield'),
                        ],
                        type: 'object'
                    ),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Category updated'),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        Category $category,
        Request $request,
        EntityManagerInterface $em,
        DtoValidator $dtoValidator,
    ): JsonResponse {
        $data = json_decode((string) $request->getContent(), true) ?? [];

        $dto = new UpdateCategoryRequest(
            $data['icon'] ?? '',
            $data['translations'] ?? []
        );

        $dtoValidator->validate($dto);

        $category->setIcon($dto->getIcon());

        foreach ($dto->getTranslations() as $locale => $name) {
            $translation = $category->getTranslation($locale);

            if ($translation) {
                $translation->setName($name);
            } else {
                $translation = new CategoryTranslation();
                $translation->setLocale((string) $locale)
                    ->setName($name);
                $category->addCategoryTranslation($translation);
                $em->persist($translation);
            }
        }

        $em->flush();

        return new JsonResponse($category->toArray());
    }

    #[OA\Delete(
        summary: 'Delete a category',
        description: 'Delete a category and its translations (Admin only).',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Category deleted'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Category $category, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($category);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
