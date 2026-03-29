<?php

namespace App\Controller;

use App\Entity\Hunt;
use App\Entity\Reward;
use App\Entity\User;
use App\Exception\ApiException;
use App\Service\PlayerProgressService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'player-gameplay', description: 'Endpoints for player gameplay (participate in hunts, claim rewards)')]
#[Route('/api/me', name: 'app_my_gameplay_')]
#[IsGranted('ROLE_USER')]
final class UserGameplayController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PlayerProgressService $progressService,
    ) {
    }

    #[OA\Post(
        summary: 'Participate in a hunt',
        description: 'Marks the user as having participated in a hunt (increments huntCount and checks badges).',
        parameters: [
            new OA\Parameter(name: 'hunt_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Participation successfully recorded',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Participation recorded'),
                        new OA\Property(property: 'huntCount', type: 'integer', example: 5),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Hunt not found'),
        ]
    )]
    #[Route('/hunts/{hunt_id}/participate', name: 'participate', methods: ['POST'])]
    public function participate(int $hunt_id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $hunt = $this->em->getRepository(Hunt::class)->find($hunt_id);

        if (!$hunt) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Hunt not found');
        }

        $user->setHuntCount(($user->getHuntCount() ?? 0) + 1);

        $this->progressService->checkAndAwardBadges($user);

        $this->em->flush();

        return new JsonResponse([
            'message' => 'Participation recorded',
            'huntCount' => $user->getHuntCount(),
        ]);
    }

    #[OA\Post(
        summary: 'Claim a reward from a completed hunt',
        description: 'Adds the hunt\'s reward to inventory, grants XP based on rarity, updates Rank, and checks badges.',
        parameters: [
            new OA\Parameter(name: 'hunt_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'locale', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'fr')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reward claimed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Reward claimed successfully'),
                        new OA\Property(property: 'reward', type: 'object', description: 'The claimed reward details'),
                        new OA\Property(
                            property: 'userStats',
                            properties: [
                                new OA\Property(property: 'experience', type: 'integer', example: 1250),
                                new OA\Property(property: 'huntCount', type: 'integer', example: 6),
                                new OA\Property(property: 'rewardCount', type: 'integer', example: 3),
                                new OA\Property(property: 'rank', type: 'object', description: 'The user\'s current rank details', nullable: true),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Hunt has no reward, reward expired, or already claimed'),
            new OA\Response(response: 404, description: 'Hunt not found'),
        ]
    )]
    #[Route('/rewards/{hunt_id}/claim', name: 'claim_reward', methods: ['POST'])]
    public function claimReward(int $hunt_id, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $locale = (string) $request->query->get('locale');

        $hunt = $this->em->getRepository(Hunt::class)->find($hunt_id);

        if (!$hunt) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Hunt not found');
        }

        $reward = $hunt->getReward();

        if (!$reward) {
            throw new ApiException(Response::HTTP_BAD_REQUEST, 'This hunt has no reward');
        }

        if ($reward->getEndDate() < new \DateTime()) {
            throw new ApiException(Response::HTTP_BAD_REQUEST, 'This reward has expired');
        }

        if ($user->getRewards()->contains($reward)) {
            throw new ApiException(Response::HTTP_BAD_REQUEST, 'You have already claimed this reward');
        }

        // Add reward to user's inventory and increment reward count
        $user->addReward($reward);
        $user->setRewardCount(($user->getRewardCount() ?? 0) + 1);

        // Grant experience points based on the hunt's rarity
        $xpToGain = $hunt->getRarity() ? $hunt->getRarity()->getExperienceGain() : 0;
        $this->progressService->addExperience($user, $xpToGain ?? 0);

        // Check for rank updates and badge awards after claiming the reward
        $this->progressService->checkAndAwardBadges($user);

        $this->em->flush();

        return new JsonResponse([
            'message' => 'Reward claimed successfully',
            'reward' => $reward->toArray($locale),
            'userStats' => [
                'experience' => $user->getExperience(),
                'huntCount' => $user->getHuntCount(),
                'rewardCount' => $user->getRewardCount(),
                'rank' => $user->getRank()?->toArray($locale),
            ],
        ]);
    }

    #[OA\Get(
        summary: 'List my earned rewards',
        description: 'Returns all rewards currently in the authenticated user\'s inventory.',
        parameters: [
            new OA\Parameter(name: 'locale', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'fr')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of earned rewards',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    #[Route('/rewards', name: 'list_rewards', methods: ['GET'])]
    public function listEarnedRewards(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $locale = (string) $request->query->get('locale');

        $earnedRewards = $user->getRewards();
        $data = array_map(fn (Reward $r) => $r->toArray($locale), $earnedRewards->toArray());

        return new JsonResponse(['data' => $data]);
    }

    #[OA\Delete(
        summary: 'Remove an earned reward from inventory',
        description: 'Removes a specific reward from the user\'s wallet (e.g., after using a promo code).',
        parameters: [
            new OA\Parameter(name: 'reward_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Reward successfully removed from inventory'),
            new OA\Response(response: 404, description: 'Reward not found in the user\'s inventory'),
        ]
    )]
    #[Route('/rewards/{reward_id}', name: 'remove_reward', methods: ['DELETE'])]
    public function removeEarnedReward(int $reward_id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $reward = $this->em->getRepository(Reward::class)->find($reward_id);

        if (!$reward || !$user->getRewards()->contains($reward)) {
            throw new ApiException(Response::HTTP_NOT_FOUND, 'Reward not found in your inventory');
        }

        $user->removeReward($reward);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
