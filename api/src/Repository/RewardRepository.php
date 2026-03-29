<?php

namespace App\Repository;

use App\Entity\Reward;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reward>
 */
class RewardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reward::class);
    }

    public function createSearchQueryBuilder(?string $search, string $sort, string $direction, ?int $companyId = null, bool $isAdmin = false): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.rewardTranslations', 'rt')
            ->leftJoin('r.hunt', 'h')
            ->addSelect('rt', 'h');

        if (!$isAdmin && null !== $companyId) {
            $qb->andWhere('h.company = :companyId')
               ->setParameter('companyId', $companyId);
        }

        if ($search) {
            $searchTerm = mb_strtolower($search);

            $qb->setParameter('q_exact', $searchTerm)
               ->setParameter('q_prefix', $searchTerm.'%')
               ->setParameter('q_contains', '%'.$searchTerm.'%');

            $qb->andWhere('LOWER(rt.title) LIKE :q_contains OR LOWER(r.code) LIKE :q_contains');

            $relevanceCase = 'CASE 
                WHEN LOWER(rt.title) = :q_exact OR LOWER(r.code) = :q_exact THEN 0
                WHEN LOWER(rt.title) LIKE :q_prefix OR LOWER(r.code) LIKE :q_prefix THEN 1
                ELSE 2
            END';

            $qb->addOrderBy($relevanceCase, 'ASC');
        } else {
            $allowedSorts = ['id', 'code', 'endDate', 'title'];
            $sortField = in_array($sort, $allowedSorts) ? $sort : 'id';
            $orderDirection = 'DESC' === strtoupper($direction) ? 'DESC' : 'ASC';

            if ('title' === $sortField) {
                $qb->orderBy('rt.title', $orderDirection);
            } else {
                $qb->orderBy('r.'.$sortField, $orderDirection);
            }
        }

        return $qb;
    }
}
