<?php

namespace App\Tests\Controller\Authentication;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApiRegistrationControllerTest extends WebTestCase {


    public function testRegisterSuccessfully(): void
    {
        $client = self::createClient();

        $payload = [
            'email' => 'test2@test.com',
            'password' => 'password',
        ];

        $options = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        $client->request('POST', '/api/register', [], [], $options,json_encode($payload));;

        self::assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('message', $responseData);

        self::assertEquals('User created', $responseData['message']);
    }

    public function testRegisterEmailMissing(): void
    {
        $client = self::createClient();

        $payload = [
            'password' => 'password',
        ];

        $options = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        $client->request('POST', '/api/register', [], [], $options,json_encode($payload));;

        self::assertResponseStatusCodeSame(422);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('errors', $responseData);

        $error = $responseData['errors'][0];

        self::assertEquals('email', $error['property']);
        self::assertEquals('Email is required', $error['message']);
    }

    public function testRegisterEmailFormat(): void
    {
        $client = self::createClient();

        $payload = [
            'email' => 'test',
            'password' => 'password',
        ];

        $options = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        $client->request('POST', '/api/register', [], [], $options,json_encode($payload));;

        self::assertResponseStatusCodeSame(422);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('errors', $responseData);

        $error = $responseData['errors'][0];

        self::assertEquals('email', $error['property']);
        self::assertEquals('Please provide a valid email address', $error['message']);
    }

    public function testRegisterPasswordMissing(): void
    {
        $client = self::createClient();

        $payload = [
            'email' => 'test2@test.com',
        ];

        $options = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        $client->request('POST', '/api/register', [], [], $options,json_encode($payload));;

        self::assertResponseStatusCodeSame(422);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('errors', $responseData);

        $error = $responseData['errors'][0];

        self::assertEquals('password', $error['property']);
        self::assertEquals('Password is required', $error['message']);
    }

    public function testRegisterPasswordFormat(): void
    {
        $client = self::createClient();

        $payload = [
            'email' => 'test2@test.com',
            'password' => 'pass',
        ];

        $options = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        $client->request('POST', '/api/register', [], [], $options,json_encode($payload));;

        self::assertResponseStatusCodeSame(422);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('errors', $responseData);

        $error = $responseData['errors'][0];

        self::assertEquals('password', $error["property"]);
        self::assertEquals('Password must be at least 8 characters long', $error["message"]);
    }

    public function testRegisterEmailAlreadyTaken(): void
    {
        $client = self::createClient();

        $alreadyRegisteredEmail = 'test0@test.com';

        $payload = [
            'email' => $alreadyRegisteredEmail,
            'password' => 'password',
        ];

        $options = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        $client->request('POST', '/api/register', [], [], $options,json_encode($payload));;

        self::assertResponseStatusCodeSame(422);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('errors', $responseData);

        $error = $responseData['errors'][0];

        self::assertEquals('email', $error['property']);
        self::assertEquals("The email $alreadyRegisteredEmail is already taken.", $error['message']);
    }
}
