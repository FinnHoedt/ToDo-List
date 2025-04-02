<?php

namespace App\Tests\Controller;

use App\Repository\CategoryRepository;
use App\Repository\TodoRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TodoControllerTest extends WebTestCase
{
    public function testTodoIndexSuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

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

        $dbUncategorized = $todoRepository->getTodosOfUserForCategory($testUser, null);
        $dbCategories = $testUser->getCategories();

        self::assertCount($dbUncategorized->count(), $uncategorizedTodos);
        self::assertCount($dbCategories->count(), $categorizedTodos);
    }

    public function testTodoIndexWithCategorySuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $category = $testUser->getCategories()->first();

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('GET', "/api/todo/category/{$category->getId()}", $options);

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertIsArray($responseData);
        self::assertNotEmpty($responseData);

        $todos = $todoRepository->getTodosOfUserForCategory($testUser, $category);

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
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $client->loginUser($testUser);

        $categoryId = $otherUser->getCategories()->last()->getId() + 1;

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('GET', "/api/todo/category/{$categoryId}", $options);

        self::assertResponseStatusCodeSame(404);
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

        $todos = $todoRepository->getTodosOfUserForCategory($testUser, null);

        self::assertCount(count($todos), $responseData);
    }

    public function testTodoCreateWithCategorySuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $category = $testUser->getCategories()->first();

        $payload = [
            "title" => "Test Todo",
            "description" => "Test Todo Description"
        ];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('POST', "/api/todo/category/{$category->getId()}", [], [], $options, json_encode($payload));

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $message = $responseData['message'];
        $todo = $responseData['todo'];

        self::assertEquals('Todo created', $message);

        self::assertIsArray($todo);

        $createdTodo = $todoRepository->find($todo['id']);

        self::assertEquals($payload['title'], $createdTodo->getTitle());
        self::assertEquals($payload['description'], $createdTodo->getDescription());
    }

    public function testTodoCreateWithCategoryNotOwned(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $client->loginUser($testUser);

        $category = $otherUser->getCategories()->first();

        $payload = [
            "title" => "Test Todo",
            "description" => "Test Todo Description"
        ];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('POST', "/api/todo/category/{$category->getId()}", [], [], $options, json_encode($payload));

        self::assertResponseStatusCodeSame(403);
    }

    public function testTodoCreateWithCategoryNotExisting(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $client->loginUser($testUser);

        $categoryId = $otherUser->getCategories()->last()->getId() + 1;

        $payload = [
            "title" => "Test Todo",
            "description" => "Test Todo Description"
        ];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('POST', "/api/todo/category/{$categoryId}", [], [], $options, json_encode($payload));

        self::assertResponseStatusCodeSame(404);
    }

    public function testTodoCreateWithCategoryValidation(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $category = $testUser->getCategories()->first();

        $payload = [
            "description" => 2
        ];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('POST', "/api/todo/category/{$category->getId()}", [], [], $options, json_encode($payload));

        self::assertResponseStatusCodeSame(422);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('errors', $responseData);

        $errors = $responseData['errors'];

        self::assertCount(2, $errors);

        self::assertEquals('title', $errors[0]['property']);
        self::assertEquals('Title is required', $errors[0]['message']);

        self::assertEquals('description', $errors[1]['property']);
        self::assertEquals('Description has to be a valid string', $errors[1]['message']);
    }

    public function testTodoCreateWithoutCategorySuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $payload = [
            "title" => "Test Todo",
            "description" => "Test Todo Description"
        ];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('POST', "/api/todo/category", [], [], $options, json_encode($payload));

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $message = $responseData['message'];
        $todo = $responseData['todo'];

        self::assertEquals('Todo created', $message);

        self::assertIsArray($todo);

        $createdTodo = $todoRepository->find($todo['id']);

        self::assertEquals($createdTodo->getTitle(), $todo['title']);
        self::assertEquals($createdTodo->getDescription(), $todo['description']);
    }

    public function testTodoCreateWithoutCategoryValidation(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $payload = [
            "description" => 2
        ];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('POST', "/api/todo/category", [], [], $options, json_encode($payload));

        self::assertResponseStatusCodeSame(422);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('errors', $responseData);

        $errors = $responseData['errors'];

        self::assertCount(2, $errors);

        self::assertEquals('title', $errors[0]['property']);
        self::assertEquals('Title is required', $errors[0]['message']);

        self::assertEquals('description', $errors[1]['property']);
        self::assertEquals('Description has to be a valid string', $errors[1]['message']);
    }

    public function testTodoShowSuccessfully(): void
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

        $client->request('GET', "/api/todo/{$todo->getId()}", $options);

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertEquals($todo->getId(), $responseData['id']);
    }

    public function testTodoShowNoAccess(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $client->loginUser($testUser);

        $todo = $todoRepository->getTodosOfUser($otherUser)->first();

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('GET', "/api/todo/{$todo->getId()}", $options);

        self::assertResponseStatusCodeSame(403);
    }

    public function testTodoShowNotExisting(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $client->loginUser($testUser);

        $todoId = $todoRepository->getTodosOfUser($otherUser)->last()->getId() + 1;

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('GET', "/api/todo/{$todoId}", $options);

        self::assertResponseStatusCodeSame(404);
    }

    public function testTodoUpdateSuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $todo = $todoRepository->getTodosOfUser($testUser)->first();

        $payload = [
            "title" => "Updated Todo",
            "description" => "Test Todo Description Updated"
        ];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('PATCH', "/api/todo/{$todo->getId()}", [], [], $options, json_encode($payload));

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $message = $responseData['message'];
        $todo = $responseData['todo'];

        self::assertEquals('Todo updated', $message);

        self::assertIsArray($todo);

        $updatedTodo = $todoRepository->find($todo['id']);

        self::assertEquals($updatedTodo->getTitle(), $payload['title']);
        self::assertEquals($updatedTodo->getDescription(), $payload['description']);
    }

    public function testTodoUpdateNoAccess(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $client->loginUser($testUser);

        $todo = $todoRepository->getTodosOfUser($otherUser)->first();

        $payload = [
            "title" => "Test Todo",
            "description" => "Test Todo Description"
        ];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('PATCH', "/api/todo/{$todo->getId()}", [], [], $options, json_encode($payload));

        self::assertResponseStatusCodeSame(403);
    }

    public function testTodoUpdateNotExisting(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $client->loginUser($testUser);

        $todoId = $todoRepository->getTodosOfUser($otherUser)->last()->getId() + 1;

        $payload = [
            "title" => "Test Todo",
            "description" => "Test Todo Description"
        ];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('PATCH', "/api/todo/{$todoId}", [], [], $options, json_encode($payload));

        self::assertResponseStatusCodeSame(404);
    }

    public function testTodoUpdateValidation(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $todo = $todoRepository->getTodosOfUser($testUser)->first();

        $payload = [
            "description" => 2
        ];

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('PATCH', "/api/todo/{$todo->getId()}", [], [], $options, json_encode($payload));

        self::assertResponseStatusCodeSame(422);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('errors', $responseData);

        $errors = $responseData['errors'];

        self::assertCount(2, $errors);

        self::assertEquals('title', $errors[0]['property']);
        self::assertEquals('Title is required', $errors[0]['message']);

        self::assertEquals('description', $errors[1]['property']);
        self::assertEquals('Description has to be a valid string', $errors[1]['message']);
    }

    public function testTodoDestroySuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $todoId = $todoRepository->getTodosOfUser($testUser)->first()->getId();

        $client->loginUser($testUser);

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('DELETE', "/api/todo/{$todoId}", $options);

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertEquals('Todo deleted', $responseData['message']);

        $deletedTodo = $todoRepository->find($todoId);
        self::assertNull($deletedTodo);

    }

    public function testTodoDestroyNoAccess(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $todoId = $todoRepository->getTodosOfUser($otherUser)->first()->getId();

        $client->loginUser($testUser);

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('DELETE', "/api/todo/{$todoId}", $options);

        self::assertResponseStatusCodeSame(403);

        $deletedTodo = $todoRepository->find($todoId);
        self::assertNotNull($deletedTodo);
    }

    public function testTodoDestroyNotExisting(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $todoId = $todoRepository->getTodosOfUser($otherUser)->last()->getId() + 1;

        $client->loginUser($testUser);

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('DELETE', "/api/todo/{$todoId}", $options);

        self::assertResponseStatusCodeSame(404);
    }

    public function testTodoToggleCompleteSuccessfully(): void
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

        $client->request('PATCH', "/api/todo/{$todo->getId()}/complete", $options);

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $message = $responseData['message'];
        $todo = $responseData['todo'];

        self::assertEquals('Todo updated', $message);

        $completedTodo = $todoRepository->find($todo['id']);

        self::assertEquals($completedTodo->getCompleted(), $todo['completed']);
    }

    public function testTodoToggleCompleteNoAccess(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $client->loginUser($testUser);

        $todo = $todoRepository->getTodosOfUser($otherUser)->first();

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('PATCH', "/api/todo/{$todo->getId()}/complete", $options);

        self::assertResponseStatusCodeSame(403);
    }

    public function testTodoToggleCompleteNotExisting(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $todoRepository = self::getContainer()->get(TodoRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');
        $otherUser = $userRepository->findOneByEmail('test1@test.com');

        $client->loginUser($testUser);

        $todoId = $todoRepository->getTodosOfUser($otherUser)->last()->getId() + 1;

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('PATCH', "/api/todo/{$todoId}/complete", $options);

        self::assertResponseStatusCodeSame(404);
    }
}
