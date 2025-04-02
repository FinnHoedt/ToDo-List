<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Todo;
use App\Entity\TodoAccess;
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
        $users = $this->createUsers($manager, 2);

        $manager->flush();

        $categories = [
            $this->createCategories($manager, $users[0], 1),
            $this->createCategories($manager, $users[1], 2),
        ];

        $manager->flush();

        foreach ($users as $index => $user) {
            $this->createTodosWithCategories($manager, $user, $categories[$index]);
        }

        $manager->flush();

        foreach ($users as $user) {
            $this->createTodosWithoutCategories($manager, $user, 2);
        }

        $this->createSharedTodo($manager, $users[0], $users[1]);

        $manager->flush();
    }

    private function createUsers(ObjectManager $manager, int $count): array
    {
        $users = [];

        for ($i = 0; $i < $count; $i++) {
            $user = new User();
            $user->setEmail("test{$i}@test.com");
            $user->setPassword($this->hasher->hashPassword($user, 'password'));

            $manager->persist($user);
            $users[] = $user;
        }

        return $users;
    }

    private function createCategories(ObjectManager $manager, User $user, int $count): array
    {
        $categories = [];

        for ($i = 0; $i < $count; $i++) {
            $category = new Category();
            $category->setTitle("Category {$i} for User {$user->getId()}");
            $category->setUser($user);

            $manager->persist($category);
            $categories[] = $category;
        }

        return $categories;
    }

    private function createTodosWithCategories(ObjectManager $manager, User $user, array $categories): void
    {
        foreach ($categories as $index => $category) {
            for ($i = 0; $i < 2; $i++) {
                $todo = new Todo();
                $todo->setTitle("Todo {$i} for Category {$category->getId()}");
                $todo->setDescription("Test Description");

                $todoAccess = new TodoAccess();
                $todoAccess->setTodo($todo);
                $todoAccess->setCategory($category);
                $todoAccess->setAssignee($user);
                $todoAccess->setPrioritization(($i + 1) * 1000);

                $manager->persist($todo);
                $manager->persist($todoAccess);
            }
        }
    }

    private function createTodosWithoutCategories(ObjectManager $manager, User $user, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $todo = new Todo();
            $todo->setTitle("Todo {$i} without Category");
            $todo->setDescription("Test Description");

            $todoAccess = new TodoAccess();
            $todoAccess->setTodo($todo);
            $todoAccess->setAssignee($user);
            $todoAccess->setPrioritization(($i + 1) * 1000);

            $manager->persist($todo);
            $manager->persist($todoAccess);
        }
    }

    private function createSharedTodo(ObjectManager $manager, User $owner, User $sharedUser): void
    {
        $todo = new Todo();
        $todo->setTitle("Shared");
        $todo->setDescription("This is a shared task.");

        $ownerAccess = new TodoAccess();
        $ownerAccess->setTodo($todo);
        $ownerAccess->setAssignee($owner);
        $ownerAccess->setPrioritization(1000);

        $sharedAccess = new TodoAccess();
        $sharedAccess->setTodo($todo);
        $sharedAccess->setAssignee($sharedUser);
        $sharedAccess->setShared(true);
        $sharedAccess->setPrioritization(1000);

        $manager->persist($todo);
        $manager->persist($ownerAccess);
        $manager->persist($sharedAccess);
    }
}
