<?php

namespace App\Tests\Controller;

use App\Repository\TodoAccessRepository;
use App\Repository\TodoRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TodoAccessControllerTest extends WebTestCase
{
    public function testTodoSharingSuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);
        $todoAccessRepository = self::getContainer()->get(TodoAccessRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $client->loginUser($testUser);

        $todo = $todoRepository->getTodosOfUser($testUser)->first();

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('POST', "/api/todo/{$todo->getId()}/share/{$otherUser->getId()}", $options);

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $message = $responseData['message'];

        self::assertEquals('Todo shared', $message);

        $todoAccess = $todoAccessRepository->getTodoAccessOfTodoForUser($todo, $otherUser);

        self::assertTrue($todoAccess->isShared());
        self::assertEquals($otherUser, $todoAccess->getAssignee());
        self::assertEquals($todo, $todoAccess->getTodo());
    }

    public function testTodoSharingToSelf(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $todo = $todoRepository->getTodosOfUser($testUser)->first();

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('POST', "/api/todo/{$todo->getId()}/share/{$testUser->getId()}", $options);

        self::assertResponseStatusCodeSame(409);
    }

    public function testTodoSharingAlreadyShared(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $client->loginUser($testUser);

        $todo = $todoRepository->findOneBy(['title' => 'Shared']);

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('POST', "/api/todo/{$todo->getId()}/share/{$otherUser->getId()}", $options);

        self::assertResponseStatusCodeSame(409);
    }

    public function testTodoPrioritizationSuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);
        $todoAccessRepository = self::getContainer()->get(TodoAccessRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $todo = $todoRepository->getTodosOfUser($testUser)->first();

        $payload = [
            "position" => 1
        ];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('PATCH', "/api/todo/{$todo->getId()}/prioritize", [], [], $options, json_encode($payload));

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $message = $responseData['message'];

        self::assertEquals('Todo priority changed', $message);

        $todoAccess = $todoAccessRepository->getTodoAccessOfTodoForUser($todo, $testUser);

        self::assertEquals(3000, $todoAccess->getPrioritization());
    }

    public function testTodoPrioritizationAlreadyInOrder(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);
        $todoAccessRepository = self::getContainer()->get(TodoAccessRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $todo = $todoRepository->getTodosOfUser($testUser)->first();

        $payload = [
            "position" => 0
        ];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('PATCH', "/api/todo/{$todo->getId()}/prioritize", [], [], $options, json_encode($payload));

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $message = $responseData['message'];

        self::assertEquals('Todo priority changed', $message);

        $todoAccess = $todoAccessRepository->getTodoAccessOfTodoForUser($todo, $testUser);

        self::assertEquals(1000, $todoAccess->getPrioritization());
    }
}
