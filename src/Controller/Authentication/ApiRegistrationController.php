<?php

namespace App\Controller\Authentication;

use App\Dto\User\UserDto;
use App\Service\Controller\ErrorMessageGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\UserRepository;

final class ApiRegistrationController extends AbstractController{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request,
                             ErrorMessageGenerator $errorMessageGenerator,
                             UserRepository $userRepository,
                             SerializerInterface $serializer): JsonResponse
    {
        $jsonContent = $request->getContent();

        $user = $serializer->deserialize($jsonContent, UserDto::class, 'json');

        $errorMessages = $errorMessageGenerator->generateErrorMessage($user);
        if($errorMessages !== null){
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $userRepository->save($user);

        return new JsonResponse(['message' => 'User created'], Response::HTTP_CREATED);
    }
}
