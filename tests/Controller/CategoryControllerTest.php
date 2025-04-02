<?php

namespace App\Tests\Controller;

use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class CategoryControllerTest extends WebTestCase
{
    public function testCategoryIndexSuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('GET', '/api/category', $options);

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $categories = $testUser->getCategories();

        self::assertCount($categories->count(), $responseData);
    }

    public function testCategoryCreateSuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $payload = [
            'title' => 'Test Category',
        ];

        $options = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        $client->request('POST', '/api/category', [], [], $options, json_encode($payload));

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $category = $responseData['category'];

        self::assertEquals('Test Category', $category['title']);
        self::assertEquals($testUser->getId(), $category['userId']);
    }

    public function testCategoryCreateTitleMissing(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $payload = [];

        $options = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        $client->request('POST', '/api/category', [], [], $options, json_encode($payload));

        self::assertResponseStatusCodeSame(422);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('errors', $responseData);

        $error = $responseData['errors'][0];

        self::assertEquals('title', $error['property']);
        self::assertEquals('Title is required', $error['message']);
    }

    public function testCategoryCreateTitleFormat(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $client->loginUser($testUser);

        $payload = [
            'title' => 'Te',
        ];

        $options = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        $client->request('POST', '/api/category', [], [], $options, json_encode($payload));

        self::assertResponseStatusCodeSame(422);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('errors', $responseData);

        $error = $responseData['errors'][0];

        self::assertEquals('title', $error['property']);
        self::assertEquals('Title is minimum 3 characters', $error['message']);
    }

    public function testCategoryShowSuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $category = $testUser->getCategories()->first();

        $categoryId = $category->getId();

        $client->loginUser($testUser);

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('GET', "/api/category/{$categoryId}", $options);

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertEquals($categoryId, $responseData['id']);
    }

    public function testCategoryShowNotOwned(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $secondUser = $userRepository->findOneByEmail('test1@test.com');

        $category = $secondUser->getCategories()->first();

        $categoryId = $category->getId();

        $client->loginUser($testUser);

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('GET', "/api/category/{$categoryId}", $options);

        self::assertResponseStatusCodeSame(403);
    }

    public function testCategoryUpdateSuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $category = $testUser->getCategories()->first();

        $categoryId = $category->getId();

        $client->loginUser($testUser);

        $payload = [
            'title' => 'Updated Category',
        ];

        $options = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        $client->request('PATCH', "/api/category/{$categoryId}", [], [], $options, json_encode($payload));

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $category = $responseData['category'];

        self::assertEquals('Updated Category', $category['title']);
        self::assertEquals($testUser->getId(), $category['userId']);
    }

    public function testCategoryUpdateNotOwned(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $secondUser = $userRepository->findOneByEmail('test1@test.com');

        $category = $secondUser->getCategories()->first();

        $categoryId = $category->getId();

        $client->loginUser($testUser);

        $payload = [
            'title' => 'Updated Category',
        ];

        $options = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        $client->request('PATCH', "/api/category/{$categoryId}", [], [], $options, json_encode($payload));

        self::assertResponseStatusCodeSame(403);

        $changedCategory = $categoryRepository->find($categoryId);
        self::assertEquals($category, $changedCategory);
    }

    public function testCategoryUpdateTitleFormat(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $category = $testUser->getCategories()->first();

        $categoryId = $category->getId();

        $client->loginUser($testUser);

        $payload = [
            'title' => 'Up',
        ];

        $options = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        $client->request('PATCH', "/api/category/{$categoryId}", [], [], $options, json_encode($payload));

        self::assertResponseStatusCodeSame(422);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('errors', $responseData);

        $error = $responseData['errors'][0];

        self::assertEquals('title', $error['property']);
        self::assertEquals('Title is minimum 3 characters', $error['message']);
    }

    public function testCategoryDestroySuccessfully(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $category = $testUser->getCategories()->first();

        $categoryId = $category->getId();

        $client->loginUser($testUser);

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('DELETE', "/api/category/{$categoryId}", $options);

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertEquals('Category deleted', $responseData['message']);

        $deletedCategory = $categoryRepository->find($categoryId);
        self::assertNull($deletedCategory);

    }

    public function testCategoryDestroyNotOwned(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);

        $testUser = $userRepository->findOneByEmail('test0@test.com');

        $secondUser = $userRepository->findOneByEmail('test1@test.com');

        $category = $secondUser->getCategories()->first();

        $categoryId = $category->getId();

        $client->loginUser($testUser);

        $options = [
            "Accept" => "application/json",
        ];

        $client->request('DELETE', "/api/category/{$categoryId}", $options);

        self::assertResponseStatusCodeSame(403);

        $deletedCategory = $categoryRepository->find($categoryId);
        self::assertNotNull($deletedCategory);
    }
}
