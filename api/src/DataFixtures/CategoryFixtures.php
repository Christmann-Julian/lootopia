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
                'icon' => 'icon-food-bev',
                'fr' => 'Restauration',
                'en' => 'Food',
            ],
            [
                'ref' => 'fashion_beauty',
                'icon' => 'icon-fashion-beauty',
                'fr' => 'Mode & Beauté',
                'en' => 'Fashion & Beauty',
            ],
            [
                'ref' => 'retail',
                'icon' => 'icon-retail',
                'fr' => 'Commerce',
                'en' => 'Retail',
            ],
            [
                'ref' => 'sport_outdoor',
                'icon' => 'icon-sport-outdoor',
                'fr' => 'Sport & Plein air',
                'en' => 'Sports & Outdoors',
            ],
            [
                'ref' => 'tech',
                'icon' => 'icon-tech',
                'fr' => 'Technologie',
                'en' => 'Tech',
            ],
            [
                'ref' => 'entertainment',
                'icon' => 'icon-entertainment',
                'fr' => 'Loisirs',
                'en' => 'Entertainment',
            ],
            [
                'ref' => 'tourism',
                'icon' => 'icon-tourism',
                'fr' => 'Tourisme',
                'en' => 'Tourism',
            ],
            [
                'ref' => 'charity',
                'icon' => 'icon-charity',
                'fr' => 'Associations & ONG',
                'en' => 'Charity & NGOs',
            ],
            [
                'ref' => 'culture',
                'icon' => 'icon-culture',
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
