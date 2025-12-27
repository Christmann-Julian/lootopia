<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RefreshToken>
 */
class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    /**
     * Mark all refresh tokens for a given user identifier as revoked (bulk update).
     *
     * @return int Number of rows affected
     */
    public function revokeAllForUser(string $userIdentifier): int
    {
        return $this->createQueryBuilder('t')
            ->update()
            ->set('t.revoked', ':revoked')
            ->where('t.userIdentifier = :uid')
            ->andWhere('t.revoked = :alreadyRevoked')
            ->setParameter('revoked', true)
            ->setParameter('alreadyRevoked', false)
            ->setParameter('uid', $userIdentifier)
            ->getQuery()
            ->execute();
    }
}
