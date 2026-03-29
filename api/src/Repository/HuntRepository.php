<?php

namespace App\Repository;

use App\Entity\Hunt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hunt>
 */
class HuntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hunt::class);
    }

    public function createSearchQueryBuilder(?string $search, string $sort, string $direction, ?int $companyId = null, bool $isAdmin = false): QueryBuilder
    {
        $qb = $this->createQueryBuilder('h')
            ->leftJoin('h.huntTranslations', 'ht')
            ->leftJoin('h.category', 'c')
            ->leftJoin('h.rarity', 'r')
            ->addSelect('ht', 'c', 'r');

        if (!$isAdmin && null !== $companyId) {
            $qb->andWhere('h.company = :companyId')
               ->setParameter('companyId', $companyId);
        }

        if ($search) {
            $searchTerm = mb_strtolower($search);

            $qb->setParameter('q_exact', $searchTerm)
               ->setParameter('q_prefix', $searchTerm.'%')
               ->setParameter('q_contains', '%'.$searchTerm.'%');

            $qb->andWhere('LOWER(ht.title) LIKE :q_contains OR LOWER(ht.location) LIKE :q_contains');

            $relevanceCase = 'CASE 
                WHEN LOWER(ht.title) = :q_exact THEN 0
                WHEN LOWER(ht.title) LIKE :q_prefix THEN 1
                ELSE 2
            END';

            $qb->addOrderBy($relevanceCase, 'ASC');
        } else {
            $allowedSorts = ['id', 'lat', 'lon'];
            $sortField = in_array($sort, $allowedSorts) ? $sort : 'id';
            $orderDirection = 'DESC' === strtoupper($direction) ? 'DESC' : 'ASC';
            $qb->orderBy('h.'.$sortField, $orderDirection);
        }

        return $qb;
    }
}
