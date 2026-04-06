<?php

namespace App\Repository;

use App\Entity\Rarity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rarity>
 */
class RarityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rarity::class);
    }

    public function createSearchQueryBuilder(?string $search, string $sort, string $direction): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.rarityTranslations', 'rt')
            ->addSelect('rt');

        if ($search) {
            $searchTerm = mb_strtolower($search);

            $qb->setParameter('q_exact', $searchTerm)
               ->setParameter('q_prefix', $searchTerm.'%')
               ->setParameter('q_contains', '%'.$searchTerm.'%');

            $qb->andWhere('LOWER(rt.name) LIKE :q_contains');

            // Tri par pertinence
            $relevanceCase = 'CASE 
                WHEN LOWER(rt.name) = :q_exact THEN 0
                WHEN LOWER(rt.name) LIKE :q_prefix THEN 1
                ELSE 2
            END';

            $qb->addOrderBy($relevanceCase, 'ASC');
        } else {
            $allowedSorts = ['id', 'minExperience', 'name'];
            $sortField = in_array($sort, $allowedSorts) ? $sort : 'minExperience';
            $orderDirection = 'DESC' === strtoupper($direction) ? 'DESC' : 'ASC';

            if ('name' === $sortField) {
                $qb->orderBy('rt.name', $orderDirection);
            } else {
                $qb->orderBy('r.'.$sortField, $orderDirection);
            }
        }

        return $qb;
    }
}
