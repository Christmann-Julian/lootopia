<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Category;
use App\Entity\Rank;
use App\Entity\User;
use App\Entity\Badge;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $rankLevel1 = $this->getReference(RankFixtures::RANK_REFERENCE_PREFIX . '1', Rank::class);
        $rankLevel4 = $this->getReference(RankFixtures::RANK_REFERENCE_PREFIX . '4', Rank::class);
        $rankMax = $this->getReference(RankFixtures::RANK_REFERENCE_PREFIX . '10', Rank::class);

        $allBadgeRefs = [
            'hunt_1', 'hunt_10', 'hunt_50', 'hunt_100',
            'reward_1', 'reward_10', 'reward_50', 'reward_100',
            'time_1w', 'time_1m', 'time_1y', 'time_5y',
        ];
        
        $badges = [];
        foreach ($allBadgeRefs as $ref) {
            $badges[$ref] = $this->getReference(BadgeFixtures::BADGE_REFERENCE_PREFIX . $ref, Badge::class);
        }

        $adminCompany = new Company();
        $adminCompany->setName('Lootopia');
        $manager->persist($adminCompany);

        $adminUser = new User();
        $adminUser->setFirstname('Admin Firstname');
        $adminUser->setLastname('Admin Lastname');
        $adminUser->setEmail('admin@lootopia.fr');
        $adminUser->setPseudo('AdminHunter');
        $adminUser->setExperience(15000);
        $adminUser->setHuntCount(150);
        $adminUser->setRewardCount(120);
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin'));
        $adminUser->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $adminUser->setIsVerified(true);
        $adminUser->setCompany($adminCompany);
        $adminUser->setRank($rankMax);

        foreach ($badges as $badge) {
            $adminUser->addBadge($badge);
        }
        $manager->persist($adminUser);

        $userCompany = new Company();
        $userCompany->setName('User Company');
        $manager->persist($userCompany);

        $userTest = new User();
        $userTest->setFirstname('User Firstname');
        $userTest->setLastname('User Lastname');
        $userTest->setEmail('user@lootopia.fr');
        $userTest->setPseudo('UserHunter');
        $userTest->setExperience(600);
        $userTest->setHuntCount(12);
        $userTest->setRewardCount(4);
        $userTest->setPassword($this->passwordHasher->hashPassword($userTest, 'user'));
        $userTest->setRoles(['ROLE_USER']);
        $userTest->setIsVerified(true);
        $userTest->setCompany($userCompany);
        $userTest->setRank($rankLevel4);

        $userTest->addBadge($badges['hunt_1']);
        $userTest->addBadge($badges['hunt_10']);
        $userTest->addBadge($badges['reward_1']);
        $userTest->addBadge($badges['time_1w']);
        $userTest->addBadge($badges['time_1m']);
        $userTest->addBadge($badges['time_1y']);
        $manager->persist($userTest);

        $brandsByCategory = [
            'food_bev'       => ['Danone', 'Paul', 'Burger King', 'Red Bull'],
            'fashion_beauty' => ['L\'Oréal', 'Chanel', 'Sephora', 'Yves Rocher'],
            'retail'         => ['Carrefour', 'E.Leclerc', 'Fnac', 'Auchan'],
            'sport_outdoor'  => ['Decathlon', 'Salomon', 'Rossignol', 'Le Coq Sportif'],
            'tech'           => ['Orange', 'Free', 'Boulanger', 'LDLC'],
            'entertainment'  => ['Pathé', 'Parc Astérix', 'Puy du Fou', 'Gaumont'],
            'tourism'        => ['Air France', 'SNCF', 'Club Med', 'Accor'],
            'charity'        => ['Les Restos du Cœur', 'Secours Populaire', 'Emmaüs', 'Croix-Rouge'],
            'culture'        => ['Le Louvre', 'Musée d\'Orsay', 'Château de Versailles', 'Centre Pompidou'],
        ];

        foreach ($brandsByCategory as $brands) {
            foreach ($brands as $brandName) {
                $company = new Company();
                $company->setName($brandName);
                $manager->persist($company);

                $user = new User();
                $user->setFirstname('Contact');
                $user->setLastname($brandName);
                
                $email = sprintf('contact@%s.fr', $this->normalizeForEmail($brandName));
                $user->setEmail($email);
                $user->setPseudo($brandName);
                $user->setExperience(0);
                $user->setHuntCount(0);
                $user->setRewardCount(0);
                $user->setPassword($this->passwordHasher->hashPassword($user, 'user'));
                $user->setRoles(['ROLE_USER']);
                $user->setIsVerified(true);
                $user->setCompany($company);
                $user->setRank($rankLevel1);
                
                $manager->persist($user);
            }
        }

        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; ++$i) {
            $firstname = $faker->firstName();
            $lastname = $faker->lastName();

            $email = sprintf(
                '%s.%s@user.fr',
                $this->normalizeForEmail($firstname),
                $this->normalizeForEmail($lastname)
            );

            $user = new User();
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setEmail($email);
            $user->setPseudo('Hunter_'.$i);
            $user->setExperience(0);
            $user->setHuntCount(0);
            $user->setRewardCount(0);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'user'));
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(true);
            $user->setRank($rankLevel1);
            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RankFixtures::class,
            BadgeFixtures::class,
        ];
    }

    private function normalizeForEmail(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]/', '', (string) $text);

        return (string) $text;
    }
}