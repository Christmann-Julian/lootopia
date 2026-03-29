<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function createSearchQueryBuilder(?string $search, string $sort, string $direction): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.categoryTranslations', 'ct')
            ->addSelect('ct');

        if ($search) {
            $searchTerm = mb_strtolower($search);

            $qb->setParameter('q_exact', $searchTerm)
               ->setParameter('q_prefix', $searchTerm.'%')
               ->setParameter('q_contains', '%'.$searchTerm.'%');

            $qb->andWhere('LOWER(c.icon) LIKE :q_contains OR LOWER(ct.name) LIKE :q_contains');

            // Tri par pertinence
            $relevanceCase = 'CASE 
                WHEN LOWER(ct.name) = :q_exact OR LOWER(c.icon) = :q_exact THEN 0
                WHEN LOWER(ct.name) LIKE :q_prefix OR LOWER(c.icon) LIKE :q_prefix THEN 2
                ELSE 3
            END';

            $qb->addOrderBy($relevanceCase, 'ASC');
        } else {
            $allowedSorts = ['id', 'icon', 'name'];
            $sortField = in_array($sort, $allowedSorts) ? $sort : 'id';
            $orderDirection = 'DESC' === strtoupper($direction) ? 'DESC' : 'ASC';

            if ('name' === $sortField) {
                $qb->orderBy('ct.name', $orderDirection);
            } else {
                $qb->orderBy('c.'.$sortField, $orderDirection);
            }
        }

        return $qb;
    }
}
