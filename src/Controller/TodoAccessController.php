<?php

namespace App\Controller;

use App\Dto\PrioritizationDto;
use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoAccessRepository;
use App\Service\Controller\ErrorMessageGenerator;
use App\Service\TodoAccessService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

final class TodoAccessController extends AbstractController
{
    #[Route('/api/todo/{todo_id}/share/{user_id}', name: 'app_todo_share', methods: ['POST'])]
    public function shareTodo(
        #[CurrentUser] User $user,
        #[MapEntity(mapping: ['todo_id' => 'id'])] Todo $todo,
        #[MapEntity(mapping: ['user_id' => 'id'])] User $userToGetShared,
        TodoAccessRepository $todoAccessRepository,
        TodoAccessService $todoAccessService): JsonResponse
    {
        if($user === $userToGetShared)
        {
            return $this->json([
                'message' => 'You cannot revoke your own access',
            ], Response::HTTP_CONFLICT);
        }

        $todoAccessService->ensureUserIsOwner($todo, $user);

        $todoAccess = $todoAccessRepository->share($todo, $userToGetShared);

        if($todoAccess === null){
            return $this->json([
                'message' => 'Todo is already shared with User',
                'todo' => $todo
            ], Response::HTTP_CONFLICT, [], ['groups' => ['todo:read', 'todoAccess:read']]);
        }

        return $this->json([
            'message' => 'Todo shared',
            'todo' => $todo
        ], Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/{todo_id}/revoke/{user_id}', name: 'app_todo_revoke', methods: ['POST'])]
    public function revokeTodo(
        #[CurrentUser] User $user,
        #[MapEntity(mapping: ['todo_id' => 'id'])] Todo $todo,
        #[MapEntity(mapping: ['user_id' => 'id'])] User $userToGetRevoked,
        TodoAccessRepository $todoAccessRepository,
        TodoAccessService $todoAccessService): JsonResponse
    {
        if($user === $userToGetRevoked){
            return $this->json([
                'message' => 'You cannot revoke your own access',
            ], Response::HTTP_CONFLICT);
        }

        $todoAccessService->ensureUserIsOwner($todo, $user);

        $successful = $todoAccessRepository->revoke($todo, $userToGetRevoked);

        if(!$successful){
            return $this->json([
                'message' => 'Todo is not shared with user',
            ], Response::HTTP_CONFLICT);
        }

        return $this->json([
            'message' => 'Todo revoked',
            'todo' => $todo
        ], Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/{todo_id}/prioritize', name: 'app_todo_prioritize', methods: ['POST'])]
    public function prioritizeTodo(
        #[CurrentUser] User $user,
        Request $request,
        #[MapEntity(mapping: ['todo_id' => 'id'])] Todo $todo,
        TodoAccessRepository $todoAccessRepository,
        TodoAccessService $todoAccessService,
        ErrorMessageGenerator $errorMessageGenerator,
        SerializerInterface $serializer): JsonResponse
    {
        $jsonContent = $request->getContent();

        $todoDto = $serializer->deserialize($jsonContent, PrioritizationDto::class, 'json');

        $errorMessages = $errorMessageGenerator->generateErrorMessage($todoDto);
        if($errorMessages !== null){
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json([
            $todoDto
        ]);
    }
}
