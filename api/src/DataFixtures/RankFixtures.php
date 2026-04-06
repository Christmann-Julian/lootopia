<?php

namespace App\DataFixtures;

use App\Entity\Rank;
use App\Entity\RankTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RankFixtures extends Fixture
{
    public const RANK_REFERENCE_PREFIX = 'rank_';

    public function load(ObjectManager $manager): void
    {
        $ranksData = [
            [
                'level' => 1,
                'min' => 0,
                'max' => 99,
                'fr' => 'Apprenti Pisteur',
                'en' => 'Apprentice Tracker',
            ],
            [
                'level' => 2,
                'min' => 100,
                'max' => 249,
                'fr' => 'Dénicheur de Babioles',
                'en' => 'Trinket Finder',
            ],
            [
                'level' => 3,
                'min' => 250,
                'max' => 499,
                'fr' => 'Décrypteur d\'Énigmes',
                'en' => 'Riddle Decoder',
            ],
            [
                'level' => 4,
                'min' => 500,
                'max' => 999,
                'fr' => 'Arpenteur de l\'Inconnu',
                'en' => 'Surveyor of the Unknown',
            ],
            [
                'level' => 5,
                'min' => 1000,
                'max' => 1999,
                'fr' => 'Chasseur de Reliques',
                'en' => 'Relic Hunter',
            ],
            [
                'level' => 6,
                'min' => 2000,
                'max' => 3499,
                'fr' => 'Pilleur de Sanctuaires',
                'en' => 'Shrine Raider',
            ],
            [
                'level' => 7,
                'min' => 3500,
                'max' => 5499,
                'fr' => 'Maître des Artéfacts',
                'en' => 'Artifact Master',
            ],
            [
                'level' => 8,
                'min' => 5500,
                'max' => 7999,
                'fr' => 'Chuchoteur de Mythes',
                'en' => 'Myth Whisperer',
            ],
            [
                'level' => 9,
                'min' => 8000,
                'max' => 11999,
                'fr' => 'Gardien des Secrets',
                'en' => 'Keeper of Secrets',
            ],
            [
                'level' => 10,
                'min' => 12000,
                'max' => 999999,
                'fr' => 'Oracle de Lootopia',
                'en' => 'Lootopia Oracle',
            ],
        ];

        foreach ($ranksData as $data) {
            $rank = new Rank();
            $rank->setLevel($data['level']);
            $rank->setExperienceMin($data['min']);
            $rank->setExperienceMax($data['max']);

            $translationFr = new RankTranslation();
            $translationFr->setLocale('fr');
            $translationFr->setName($data['fr']);

            $translationEn = new RankTranslation();
            $translationEn->setLocale('en');
            $translationEn->setName($data['en']);

            $rank->addRankTranslation($translationFr);
            $rank->addRankTranslation($translationEn);

            $manager->persist($translationFr);
            $manager->persist($translationEn);
            $manager->persist($rank);

            $this->addReference(self::RANK_REFERENCE_PREFIX.$data['level'], $rank);
        }

        $manager->flush();
    }
}
