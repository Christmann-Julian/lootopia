<?php

namespace App\Repository;

use App\Entity\Badge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Badge>
 */
class BadgeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Badge::class);
    }

    public function createSearchQueryBuilder(?string $search, string $sort, string $direction): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.badgeTranslations', 'bt')
            ->addSelect('bt');

        if ($search) {
            $searchTerm = mb_strtolower($search);

            $qb->setParameter('q_exact', $searchTerm)
               ->setParameter('q_prefix', $searchTerm.'%')
               ->setParameter('q_contains', '%'.$searchTerm.'%');

            $qb->andWhere('LOWER(b.icon) LIKE :q_contains OR LOWER(bt.name) LIKE :q_contains');

            // Tri par pertinence
            $relevanceCase = 'CASE 
                WHEN LOWER(bt.name) = :q_exact OR LOWER(b.icon) = :q_exact THEN 0
                WHEN LOWER(bt.name) LIKE :q_prefix OR LOWER(b.icon) LIKE :q_prefix THEN 2
                ELSE 3
            END';

            $qb->addOrderBy($relevanceCase, 'ASC');
        } else {
            $allowedSorts = ['id', 'icon', 'name'];
            $sortField = in_array($sort, $allowedSorts) ? $sort : 'id';
            $orderDirection = 'DESC' === strtoupper($direction) ? 'DESC' : 'ASC';

            if ('name' === $sortField) {
                $qb->orderBy('bt.name', $orderDirection);
            } else {
                $qb->orderBy('b.'.$sortField, $orderDirection);
            }
        }

        return $qb;
    }
}
