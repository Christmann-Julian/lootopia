<?php

namespace App\DataFixtures;

use App\Entity\Badge;
use App\Entity\BadgeTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BadgeFixtures extends Fixture
{
    public const BADGE_REFERENCE_PREFIX = 'badge_';

    public function load(ObjectManager $manager): void
    {
        $badgesData = [
            [
                'ref' => 'hunt_1',
                'icon' => 'target',
                'fr' => 'Chasseur débutant',
                'en' => 'Beginner Hunter',
            ],
            [
                'ref' => 'hunt_10',
                'icon' => 'target',
                'fr' => 'Chasseur expert',
                'en' => 'Expert Hunter',
            ],
            [
                'ref' => 'hunt_50',
                'icon' => 'target',
                'fr' => 'Chasseur légendaire',
                'en' => 'Legendary Hunter',
            ],
            [
                'ref' => 'hunt_100',
                'icon' => 'star',
                'fr' => 'Chasseur mythique',
                'en' => 'Mythic Hunter',
            ],
            [
                'ref' => 'reward_1',
                'icon' => 'trophy',
                'fr' => 'Premier butin',
                'en' => 'First Loot',
            ],
            [
                'ref' => 'reward_10',
                'icon' => 'trophy',
                'fr' => 'Chasseur de trésors',
                'en' => 'Treasure Hunter',
            ],
            [
                'ref' => 'reward_50',
                'icon' => 'trophy',
                'fr' => 'Collectionneur de reliques',
                'en' => 'Relic Collector',
            ],
            [
                'ref' => 'reward_100',
                'icon' => 'star',
                'fr' => 'Maître des butins',
                'en' => 'Loot Master',
            ],
            [
                'ref' => 'time_1w',
                'icon' => 'zap',
                'fr' => 'Survivant d\'1 semaine',
                'en' => '1 Week Survivor',
            ],
            [
                'ref' => 'time_1m',
                'icon' => 'zap',
                'fr' => 'Explorateur d\'1 mois',
                'en' => '1 Month Explorer',
            ],
            [
                'ref' => 'time_1y',
                'icon' => 'zap',
                'fr' => 'Vétéran d\'1 an',
                'en' => '1 Year Veteran',
            ],
            [
                'ref' => 'time_5y',
                'icon' => 'zap',
                'fr' => 'Ancien de 5 ans',
                'en' => '5 Years Elder',
            ],
        ];

        foreach ($badgesData as $data) {
            $badge = new Badge();
            $badge->setIcon($data['icon']);

            $translationFr = new BadgeTranslation();
            $translationFr->setLocale('fr');
            $translationFr->setName($data['fr']);

            $translationEn = new BadgeTranslation();
            $translationEn->setLocale('en');
            $translationEn->setName($data['en']);

            $badge->addBadgeTranslation($translationFr);
            $badge->addBadgeTranslation($translationEn);

            $manager->persist($translationFr);
            $manager->persist($translationEn);
            $manager->persist($badge);

            $this->addReference(self::BADGE_REFERENCE_PREFIX.$data['ref'], $badge);
        }

        $manager->flush();
    }
}
