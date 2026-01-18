<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $adminUser = new User();
        $adminUser->setFirstname('Admin Firstname');
        $adminUser->setLastname('Admin Lastname');
        $adminUser->setEmail('admin@lootopia.fr');
        $adminUser->setCompany('Lootopia');
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin'));
        $adminUser->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $adminUser->setIsVerified(true);

        $manager->persist($adminUser);

        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 35; ++$i) {
            $firstname = $faker->firstName();
            $lastname = $faker->lastName();
            $company = $faker->boolean(66) ? $faker->company() : null;

            $user = new User();
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setEmail(strtolower($firstname.'.'.$lastname.'@user.com'));
            $user->setCompany($company);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'user'));
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(true);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
