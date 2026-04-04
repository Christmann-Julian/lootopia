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
                'ref'  => 'hunt_1',
                'icon' => 'icon-hunt-beginner',
                'fr'   => 'Chasseur débutant',
                'en'   => 'Beginner Hunter',
            ],
            [
                'ref'  => 'hunt_10',
                'icon' => 'icon-hunt-expert',
                'fr'   => 'Chasseur expert',
                'en'   => 'Expert Hunter',
            ],
            [
                'ref'  => 'hunt_50',
                'icon' => 'icon-hunt-legendary',
                'fr'   => 'Chasseur légendaire',
                'en'   => 'Legendary Hunter',
            ],
            [
                'ref'  => 'hunt_100',
                'icon' => 'icon-hunt-mythic',
                'fr'   => 'Chasseur mythique',
                'en'   => 'Mythic Hunter',
            ],
            [
                'ref'  => 'reward_1',
                'icon' => 'icon-reward-first',
                'fr'   => 'Premier butin',
                'en'   => 'First Loot',
            ],
            [
                'ref'  => 'reward_10',
                'icon' => 'icon-reward-treasure',
                'fr'   => 'Chasseur de trésors',
                'en'   => 'Treasure Hunter',
            ],
            [
                'ref'  => 'reward_50',
                'icon' => 'icon-reward-relic',
                'fr'   => 'Collectionneur de reliques',
                'en'   => 'Relic Collector',
            ],
            [
                'ref'  => 'reward_100',
                'icon' => 'icon-reward-master',
                'fr'   => 'Maître des butins',
                'en'   => 'Loot Master',
            ],
            [
                'ref'  => 'time_1w',
                'icon' => 'icon-time-week',
                'fr'   => 'Survivant d\'1 semaine',
                'en'   => '1 Week Survivor',
            ],
            [
                'ref'  => 'time_1m',
                'icon' => 'icon-time-month',
                'fr'   => 'Explorateur d\'1 mois',
                'en'   => '1 Month Explorer',
            ],
            [
                'ref'  => 'time_1y',
                'icon' => 'icon-time-year',
                'fr'   => 'Vétéran d\'1 an',
                'en'   => '1 Year Veteran',
            ],
            [
                'ref'  => 'time_5y',
                'icon' => 'icon-time-legend',
                'fr'   => 'Ancien de 5 ans',
                'en'   => '5 Years Elder',
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

            $this->addReference(self::BADGE_REFERENCE_PREFIX . $data['ref'], $badge);
        }

        $manager->flush();
    }
}