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

        $userTest = new User();
        $userTest->setFirstname('User Firstname');
        $userTest->setLastname('User Lastname');
        $userTest->setEmail('user@lootopia.fr');
        $userTest->setCompany('Lootopia');
        $userTest->setPassword($this->passwordHasher->hashPassword($userTest, 'user'));
        $userTest->setRoles(['ROLE_USER']);
        $userTest->setIsVerified(true);

        $manager->persist($userTest);

        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 35; ++$i) {
            $firstname = $faker->firstName();
            $lastname = $faker->lastName();
            $company = $faker->boolean(66) ? $faker->company() : null;

            $email = sprintf(
                '%s.%s@user.fr',
                $this->normalizeForEmail($firstname),
                $this->normalizeForEmail($lastname),
            );

            $user = new User();
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setEmail($email);
            $user->setCompany($company);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'user'));
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(true);

            $manager->persist($user);
        }

        $manager->flush();
    }

    private function normalizeForEmail(string $text): string
    {
        $text = str_replace(' ', '', $text);

        $text = strtolower($text);

        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

        $text = preg_replace('/[^a-z0-9]/', '', (string) $text);

        return (string) $text;
    }
}
