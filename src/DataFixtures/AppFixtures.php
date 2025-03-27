<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        //TestUser
        $users = [];
        for ($i = 0; $i < 2; $i++) {
            $user = new User();
            $user->setEmail("test{$i}@test.com");
            $password = $this->hasher->hashPassword($user, 'password');
            $user->setPassword($password);
            $manager->persist($user);

            $users[] = $user;
        }

        //Category
        for ($i = 0; $i < 6; $i++) {
            $category = new Category();
            $category->setTitle('Category ' . $i);

            $category->setUser($users[$i % count($users)]);

            $manager->persist($category);
        }

        $manager->flush();
    }
}
