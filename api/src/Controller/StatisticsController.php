<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\CompanyRepository;
use App\Repository\HuntRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'statistics', description: 'Endpoints for dashboard statistics')]
#[Route('/api/statistics', name: 'app_statistics_')]
final class StatisticsController extends AbstractController
{
    #[OA\Get(
        summary: 'Get Admin Statistics',
        description: 'Returns 3 key metrics for the administrator dashboard.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Admin statistics',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'totalUsers', type: 'integer', example: 1250),
                        new OA\Property(property: 'totalHunts', type: 'integer', example: 342),
                        new OA\Property(property: 'totalCompanies', type: 'integer', example: 45),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 403, description: 'Forbidden (Requires Admin role)'),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin', methods: ['GET'])]
    public function adminStats(
        UserRepository $userRepository,
        HuntRepository $huntRepository,
        CompanyRepository $companyRepository,
    ): JsonResponse {
        $totalUsers = $userRepository->count([]);
        $totalHunts = $huntRepository->count([]);
        $totalCompanies = $companyRepository->count([]);

        return new JsonResponse([
            'totalUsers' => $totalUsers,
            'totalHunts' => $totalHunts,
            'totalCompanies' => $totalCompanies,
        ]);
    }

    #[OA\Get(
        summary: 'Get Company Statistics',
        description: 'Returns 3 key metrics for the authenticated company dashboard.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Company statistics',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'totalHuntsCreated', type: 'integer', example: 12),
                        new OA\Property(property: 'totalUniqueParticipants', type: 'integer', example: 85),
                        new OA\Property(property: 'totalRewardsClaimed', type: 'integer', example: 120),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 403, description: 'Forbidden (Requires User role and an associated Company)'),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('/company', name: 'company', methods: ['GET'])]
    public function companyStats(
        HuntRepository $huntRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            throw new ApiException(Response::HTTP_FORBIDDEN, 'You are not associated with any company to view these stats.');
        }

        $totalHuntsCreated = $huntRepository->count(['company' => $company]);

        $rewardsClaimedQuery = $em->createQuery('
            SELECT COUNT(r.id) 
            FROM App\Entity\User u 
            JOIN u.rewards r 
            JOIN r.hunt h 
            WHERE h.company = :company
        ')->setParameter('company', $company);

        $totalRewardsClaimed = (int) $rewardsClaimedQuery->getSingleScalarResult();

        $uniqueParticipantsQuery = $em->createQuery('
            SELECT COUNT(DISTINCT u.id) 
            FROM App\Entity\User u 
            JOIN u.rewards r 
            JOIN r.hunt h 
            WHERE h.company = :company
        ')->setParameter('company', $company);

        $totalUniqueParticipants = (int) $uniqueParticipantsQuery->getSingleScalarResult();

        return new JsonResponse([
            'totalHuntsCreated' => $totalHuntsCreated,
            'totalUniqueParticipants' => $totalUniqueParticipants,
            'totalRewardsClaimed' => $totalRewardsClaimed,
        ]);
    }

    #[OA\Get(
        summary: 'Get Admin Charts Data',
        description: 'Returns time-series and distribution data for admin charts.',
        parameters: [
            new OA\Parameter(name: 'locale', in: 'query', schema: new OA\Schema(type: 'string', default: 'fr')),
        ]
    )]
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/charts', name: 'admin_charts', methods: ['GET'])]
    public function adminCharts(UserRepository $userRepository, EntityManagerInterface $em, Request $request): JsonResponse
    {
        $locale = is_string($request->query->get('locale')) ? (string) $request->query->get('locale') : 'fr';

        $weekAgo = new \DateTimeImmutable('-6 days');
        $users = $userRepository->createQueryBuilder('u')
            ->select('u.createdAt')
            ->where('u.createdAt >= :weekAgo')
            ->setParameter('weekAgo', $weekAgo->setTime(0, 0, 0))
            ->getQuery()
            ->getResult();

        $registrationsChart = [];
        for ($i = 6; $i >= 0; --$i) {
            $date = (new \DateTimeImmutable("-$i days"))->format('d/m');
            $registrationsChart[$date] = 0;
        }

        foreach ($users as $u) {
            $date = clone $u['createdAt'];
            $dateStr = $date->format('d/m');
            if (isset($registrationsChart[$dateStr])) {
                ++$registrationsChart[$dateStr];
            }
        }

        $registrationsFormatted = [];
        foreach ($registrationsChart as $date => $count) {
            $registrationsFormatted[] = ['name' => $date, 'value' => $count];
        }

        $categoryDistribution = $em->createQuery('
            SELECT ct.name as name, COUNT(h.id) as value
            FROM App\Entity\Hunt h
            JOIN h.category c
            JOIN c.categoryTranslations ct
            WHERE ct.locale = :locale
            GROUP BY c.id, ct.name
        ')
        ->setParameter('locale', $locale)
        ->getArrayResult();

        return new JsonResponse([
            'registrations' => $registrationsFormatted,
            'categoryDistribution' => $categoryDistribution,
        ]);
    }

    #[OA\Get(
        summary: 'Get Company Charts Data',
        description: 'Returns distribution data for company charts.',
        parameters: [
            new OA\Parameter(name: 'locale', in: 'query', schema: new OA\Schema(type: 'string', default: 'fr')),
        ]
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('/company/charts', name: 'company_charts', methods: ['GET'])]
    public function companyCharts(EntityManagerInterface $em, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            throw new ApiException(Response::HTTP_FORBIDDEN, 'You are not associated with any company.');
        }

        $locale = is_string($request->query->get('locale')) ? (string) $request->query->get('locale') : 'fr';

        $rarityDistribution = $em->createQuery('
            SELECT rt.name as name, COUNT(h.id) as value
            FROM App\Entity\Hunt h
            JOIN h.rarity r
            JOIN r.rarityTranslations rt
            WHERE h.company = :company AND rt.locale = :locale
            GROUP BY r.id, rt.name
        ')
        ->setParameter('company', $company)
        ->setParameter('locale', $locale)
        ->getArrayResult();

        $categoryDistribution = $em->createQuery('
            SELECT ct.name as name, COUNT(h.id) as value
            FROM App\Entity\Hunt h
            JOIN h.category c
            JOIN c.categoryTranslations ct
            WHERE h.company = :company AND ct.locale = :locale
            GROUP BY c.id, ct.name
        ')
        ->setParameter('company', $company)
        ->setParameter('locale', $locale)
        ->getArrayResult();

        return new JsonResponse([
            'rarityDistribution' => $rarityDistribution,
            'categoryDistribution' => $categoryDistribution,
        ]);
    }
}
