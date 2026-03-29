<?php

namespace App\Service;

use App\Entity\Badge;
use App\Entity\Rank;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PlayerProgressService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Add experience points to the user and update their rank if necessary.
     */
    public function addExperience(User $user, int $amount): void
    {
        $currentXp = $user->getExperience() ?? 0;
        $newXp = $currentXp + $amount;
        $user->setExperience($newXp);

        $newRank = $this->em->getRepository(Rank::class)->createQueryBuilder('r')
            ->where('r.experienceMin <= :xp')
            ->andWhere('r.experienceMax >= :xp')
            ->setParameter('xp', $newXp)
            ->getQuery()
            ->getOneOrNullResult();

        if ($newRank && $user->getRank() !== $newRank) {
            $user->setRank($newRank);
        }
    }

    /**
     * Checks and awards new badges to the player based on their statistics.
     */
    public function checkAndAwardBadges(User $user): void
    {
        $badgesRepository = $this->em->getRepository(Badge::class);
        $allBadges = $badgesRepository->findAll();

        foreach ($allBadges as $badge) {
            if ($user->getBadges()->contains($badge)) {
                continue;
            }

            $badgeNameFr = $badge->getTranslation('fr')?->getName() ?? '';
            $shouldAward = false;

            // Participation badges
            if (str_contains(strtolower($badgeNameFr), 'chasseur débutant') && $user->getHuntCount() >= 1) {
                $shouldAward = true;
            }
            if (str_contains(strtolower($badgeNameFr), 'chasseur expert') && $user->getHuntCount() >= 10) {
                $shouldAward = true;
            }
            if (str_contains(strtolower($badgeNameFr), 'chasseur légendaire') && $user->getHuntCount() >= 50) {
                $shouldAward = true;
            }
            if (str_contains(strtolower($badgeNameFr), 'chasseur mythique') && $user->getHuntCount() >= 100) {
                $shouldAward = true;
            }

            // Rewards badges
            if (str_contains(strtolower($badgeNameFr), 'premier butin') && $user->getRewardCount() >= 1) {
                $shouldAward = true;
            }
            if (str_contains(strtolower($badgeNameFr), 'chasseur de trésors') && $user->getRewardCount() >= 10) {
                $shouldAward = true;
            }
            if (str_contains(strtolower($badgeNameFr), 'collectionneur de reliques') && $user->getRewardCount() >= 50) {
                $shouldAward = true;
            }
            if (str_contains(strtolower($badgeNameFr), 'maître des butins') && $user->getRewardCount() >= 100) {
                $shouldAward = true;
            }

            // Seniority badges
            $daysSinceCreation = $user->getCreatedAt() ? $user->getCreatedAt()->diff(new \DateTime())->days : 0;
            if (str_contains(strtolower($badgeNameFr), '1 semaine') && $daysSinceCreation >= 7) {
                $shouldAward = true;
            }
            if (str_contains(strtolower($badgeNameFr), '1 mois') && $daysSinceCreation >= 30) {
                $shouldAward = true;
            }
            if (str_contains(strtolower($badgeNameFr), '1 an') && $daysSinceCreation >= 365) {
                $shouldAward = true;
            }
            if (str_contains(strtolower($badgeNameFr), '5 ans') && $daysSinceCreation >= 365 * 5) {
                $shouldAward = true;
            }

            if ($shouldAward) {
                $user->addBadge($badge);
            }
        }
    }
}
