<?php

namespace App\DataFixtures;

use App\Entity\Rarity;
use App\Entity\RarityTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RarityFixtures extends Fixture
{
    public const RARITY_REFERENCE_PREFIX = 'rarity_';

    public function load(ObjectManager $manager): void
    {
        $raritiesData = [
            [
                'ref' => 'common',
                'minExperience' => 0,       // Rank 1
                'experienceGain' => 10,
                'fr' => 'Commun',
                'en' => 'Common',
            ],
            [
                'ref' => 'rare',
                'minExperience' => 500,     // Rank 4
                'experienceGain' => 25,
                'fr' => 'Rare',
                'en' => 'Rare',
            ],
            [
                'ref' => 'epic',
                'minExperience' => 2000,    // Rank 6
                'experienceGain' => 50,
                'fr' => 'Épique',
                'en' => 'Epic',
            ],
            [
                'ref' => 'legendary',
                'minExperience' => 5500,    // Rank 8
                'experienceGain' => 100,
                'fr' => 'Légendaire',
                'en' => 'Legendary',
            ],
        ];

        foreach ($raritiesData as $data) {
            $rarity = new Rarity();
            $rarity->setMinExperience($data['minExperience']);
            $rarity->setExperienceGain($data['experienceGain']);

            $translationFr = new RarityTranslation();
            $translationFr->setLocale('fr');
            $translationFr->setName($data['fr']);

            $translationEn = new RarityTranslation();
            $translationEn->setLocale('en');
            $translationEn->setName($data['en']);

            $rarity->addRarityTranslation($translationFr);
            $rarity->addRarityTranslation($translationEn);

            $manager->persist($translationFr);
            $manager->persist($translationEn);
            $manager->persist($rarity);

            $this->addReference(self::RARITY_REFERENCE_PREFIX.$data['ref'], $rarity);
        }

        $manager->flush();
    }
}
