<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const CATEGORY_REFERENCE_PREFIX = 'category_';

    public function load(ObjectManager $manager): void
    {
        $categoriesData = [
            [
                'ref' => 'food_bev',
                'icon' => 'hamburger',
                'fr' => 'Restauration',
                'en' => 'Food',
            ],
            [
                'ref' => 'fashion_beauty',
                'icon' => 'shirt',
                'fr' => 'Mode & Beauté',
                'en' => 'Fashion & Beauty',
            ],
            [
                'ref' => 'retail',
                'icon' => 'retail',
                'fr' => 'Commerce',
                'en' => 'Retail',
            ],
            [
                'ref' => 'sport_outdoor',
                'icon' => 'sport',
                'fr' => 'Sport & Plein air',
                'en' => 'Sports & Outdoors',
            ],
            [
                'ref' => 'tech',
                'icon' => 'tech',
                'fr' => 'Technologie',
                'en' => 'Tech',
            ],
            [
                'ref' => 'entertainment',
                'icon' => 'entertainment',
                'fr' => 'Loisirs',
                'en' => 'Entertainment',
            ],
            [
                'ref' => 'tourism',
                'icon' => 'compass',
                'fr' => 'Tourisme',
                'en' => 'Tourism',
            ],
            [
                'ref' => 'charity',
                'icon' => 'charity',
                'fr' => 'Associations & ONG',
                'en' => 'Charity & NGOs',
            ],
            [
                'ref' => 'culture',
                'icon' => 'culture',
                'fr' => 'Culture & Arts',
                'en' => 'Culture & Arts',
            ],
        ];

        foreach ($categoriesData as $data) {
            $category = new Category();
            $category->setIcon($data['icon']);

            $translationFr = new CategoryTranslation();
            $translationFr->setLocale('fr');
            $translationFr->setName($data['fr']);

            $translationEn = new CategoryTranslation();
            $translationEn->setLocale('en');
            $translationEn->setName($data['en']);

            $category->addCategoryTranslation($translationFr);
            $category->addCategoryTranslation($translationEn);

            $manager->persist($translationFr);
            $manager->persist($translationEn);
            $manager->persist($category);

            $this->addReference(self::CATEGORY_REFERENCE_PREFIX.$data['ref'], $category);
        }

        $manager->flush();
    }
}
