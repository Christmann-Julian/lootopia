<?php

namespace App\Controller;

use App\Dto\Reward\UpdateRewardRequest;
use App\Entity\Reward;
use App\Entity\RewardTranslation;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\RewardRepository;
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
    description: 'Optional locale (ex: fr, en)',
    schema: new OA\Schema(type: 'string', example: 'fr')
)]
#[OA\Tag(name: 'reward', description: 'Reward management endpoints')]
#[Route('/api/rewards', name: 'app_reward_')]
final class RewardController extends AbstractController
{
    /**
     * Checks if the current user can manage (edit) the given reward.
     */
    private function canManageReward(Reward $reward): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if ($currentUser && $currentUser->getCompany() && $reward->getHunt() && $reward->getHunt()->getCompany() === $currentUser->getCompany()) {
            return true;
        }

        return false;
    }

    #[OA\Get(
        summary: 'List rewards (Admin/User)',
        description: 'Returns a paginated list of rewards. Admins see all, users see only those linked to their company.',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'q', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', default: 'id')),
            new OA\Parameter(name: 'direction', in: 'query', schema: new OA\Schema(type: 'string', default: 'asc')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of rewards'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(RewardRepository $rewardRepository, Request $request, PaginatorInterface $paginator): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = (int) max(5, min(100, $request->query->getInt('limit', 10)));
        $search = $request->query->get('q');
        $search = is_string($search) ? $search : null;
        $sort = (string) $request->query->get('sort', 'id');
        $direction = (string) $request->query->get('direction', 'desc');
        $locale = (string) $request->query->get('locale');

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $companyId = $currentUser->getCompany()?->getId();

        $queryBuilder = $rewardRepository->createSearchQueryBuilder($search, $sort, $direction, $companyId, $isAdmin);

        $pagination = $paginator->paginate(
            $queryBuilder,
            $page,
            $limit,
            ['distinct' => true]
        );

        $rewardsData = array_map(fn (Reward $r) => $r->toArray($locale), iterator_to_array($pagination->getItems()));

        return new JsonResponse([
            'data' => $rewardsData,
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
        summary: 'Get reward details',
        responses: [
            new OA\Response(response: 200, description: 'Reward details'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Reward not found'),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'details', methods: ['GET'])]
    public function getDetails(Reward $reward, Request $request): JsonResponse
    {
        return new JsonResponse($reward->toArray((string) $request->query->get('locale')));
    }

    #[OA\Put(
        summary: 'Update a reward',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'code', type: 'string', example: 'NEWPROMO'),
                    new OA\Property(property: 'link', type: 'string', example: 'https://example.com/promo2'),
                    new OA\Property(property: 'endDate', type: 'string', format: 'date-time'),
                    new OA\Property(
                        property: 'translations',
                        properties: [
                            new OA\Property(property: 'fr', type: 'string', example: 'Nouvelle Récompense'),
                            new OA\Property(property: 'en', type: 'string', example: 'New Reward'),
                        ],
                        type: 'object'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Reward updated'),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        Reward $reward,
        Request $request,
        EntityManagerInterface $em,
        DtoValidator $dtoValidator,
    ): JsonResponse {
        if (!$this->canManageReward($reward)) {
            throw new ApiException(Response::HTTP_FORBIDDEN, 'You cannot edit this reward');
        }

        $data = json_decode((string) $request->getContent(), true) ?? [];

        $dto = new UpdateRewardRequest(
            $data['code'] ?? $reward->getCode(),
            $data['link'] ?? $reward->getLink(),
            $data['endDate'] ?? $reward->getEndDate()?->format('Y-m-d\TH:i:sP'),
            $data['translations'] ?? []
        );

        $dtoValidator->validate($dto);

        $reward->setCode($dto->getCode())
               ->setLink($dto->getLink())
               ->setEndDate($dto->getEndDate());

        foreach ($dto->getTranslations() as $locale => $title) {
            $translation = $reward->getTranslation((string) $locale);

            if ($translation) {
                $translation->setTitle($title);
            } else {
                $translation = new RewardTranslation();
                $translation->setLocale((string) $locale)
                    ->setTitle($title);
                $reward->addRewardTranslation($translation);
                $em->persist($translation);
            }
        }

        $em->flush();

        return new JsonResponse($reward->toArray());
    }
}
