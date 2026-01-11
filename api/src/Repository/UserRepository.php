<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function createSearchQueryBuilder(?string $search, string $sort, string $direction, ?int $currentUserId = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u');

        if (null !== $currentUserId) {
            $qb->andWhere('u.id != :currentUserId')
               ->setParameter('currentUserId', $currentUserId);
        }

        if ($search) {
            $searchTerm = mb_strtolower($search);

            $qb->setParameter('q_exact', $searchTerm)
               ->setParameter('q_prefix', $searchTerm.'%')
               ->setParameter('q_contains', '%'.$searchTerm.'%');

            $qb->andWhere('CONCAT(LOWER(u.lastname), LOWER(u.firstname)) LIKE :q_contains');
            $qb->orWhere('LOWER(u.email) LIKE :q_contains');

            $relevanceCase = 'CASE 
                    WHEN LOWER(u.email) = :q_exact OR LOWER(u.firstname) = :q_exact OR LOWER(u.lastname) = :q_exact THEN 0
                    WHEN LOWER(u.email) LIKE :q_prefix OR LOWER(u.firstname) LIKE :q_prefix OR LOWER(u.lastname) LIKE :q_prefix THEN 2
                    ELSE 3
                END';

            $qb->addOrderBy($relevanceCase, 'ASC');
        } else {
            $allowedSorts = ['id', 'firstname', 'lastname', 'email', 'company', 'roles'];
            $sortField = in_array($sort, $allowedSorts) ? $sort : 'id';
            $orderDirection = 'DESC' === strtoupper($direction) ? 'DESC' : 'ASC';

            $qb->orderBy('u.'.$sortField, $orderDirection);
        }

        return $qb;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
