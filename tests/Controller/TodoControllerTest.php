<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\TodoRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class TodoControllerTest extends WebTestCase
{
    public function testTodoIndexSuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('GET', '/api/todo', $options);

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $uncategorizedTodos = $responseData['uncategorized'];
        $categorizedTodos = $responseData['categorized'];

        self::assertCount(2, $uncategorizedTodos);
        self::assertCount(2, $categorizedTodos);
    }

    public function testTodoIndexWithCategorySuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $category = $testUser->getCategories()[0];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('GET', "/api/todo/category/{$category->getId()}", $options);

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertIsArray($responseData);
        self::assertNotEmpty($responseData);

        $todos = $todoRepository->getTodosOfUserOfSpecificCategory($testUser, $category);

        self::assertCount(count($todos), $responseData);
    }

    public function testTodoIndexWithCategoryNotOwned(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $client->loginUser($testUser);

        $category = $otherUser->getCategories()[0];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('GET', "/api/todo/category/{$category->getId()}", $options);

        self::assertResponseStatusCodeSame(403);
    }

    public function testTodoIndexWithCategoryNotExisting(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $categoryId = $testUser->getCategories()[1]->getId() + 1;

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('GET', "/api/todo/category/{$categoryId}", $options);

        self::assertResponseStatusCodeSame(403);
    }

    public function testTodoIndexWithoutCategorySuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('GET', "/api/todo/category", $options);

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertIsArray($responseData);
        self::assertNotEmpty($responseData);

        $todos = $todoRepository->getTodosOfUserOfSpecificCategory($testUser, null);

        self::assertCount(count($todos), $responseData);
    }
}
