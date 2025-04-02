<?php

namespace App\Controller;

use App\Dto\PrioritizationDto;
use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoAccessRepository;
use App\Service\Controller\ErrorMessageGenerator;
use App\Service\PrioritizationService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

final class TodoAccessController extends AbstractController
{
    #[Route('/api/todo/{todo_id}/share/{user_id}',
        name: 'app_todo_share',
        requirements: ['todo_id' => '\d+', 'user_id' => '\d+'],
        methods: ['POST'])]
    #[IsGranted('TODO_OWNER', 'todo')]
    public function shareTodo(
        #[CurrentUser] User $user,
        #[MapEntity(mapping: ['todo_id' => 'id'])] Todo $todo,
        #[MapEntity(mapping: ['user_id' => 'id'],)] User $userToGetShared,
        TodoAccessRepository $todoAccessRepository): JsonResponse
    {
        if($user === $userToGetShared)
        {
            return $this->json([
                'message' => 'You cannot revoke your own access',
            ], Response::HTTP_CONFLICT);
        }

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

    #[Route('/api/todo/{todo_id}/share/{user_id}',
        name: 'app_todo_revoke',
        requirements: ['todo_id' => '\d+', 'user_id' => '\d+'],
        methods: ['DELETE'])]
    #[IsGranted('TODO_OWNER', 'todo')]
    public function revokeTodo(
        #[CurrentUser] User $user,
        #[MapEntity(mapping: ['todo_id' => 'id'])] Todo $todo,
        #[MapEntity(mapping: ['user_id' => 'id'])] User $userToGetRevoked,
        TodoAccessRepository $todoAccessRepository): JsonResponse
    {
        if($user === $userToGetRevoked){
            return $this->json([
                'message' => 'You cannot revoke your own access',
            ], Response::HTTP_CONFLICT);
        }
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

    #[Route('/api/todo/{todo_id}/prioritize', name: 'app_todo_prioritize', methods: ['PATCH'])]
    #[IsGranted('TODO_ACCESS', 'todo')]
    public function prioritizeTodo(
        #[CurrentUser] User $user,
        Request $request,
        #[MapEntity(mapping: ['todo_id' => 'id'])] Todo $todo,
        TodoAccessRepository $todoAccessRepository,
        PrioritizationService $prioritizationService,
        ErrorMessageGenerator $errorMessageGenerator,
        SerializerInterface $serializer): JsonResponse
    {
        $jsonContent = $request->getContent();

        $prioritizationDto = $serializer->deserialize($jsonContent, PrioritizationDto::class, 'json');

        $errorMessages = $errorMessageGenerator->generateErrorMessage($prioritizationDto);
        if($errorMessages !== null){
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $newPriority = $prioritizationService->prioritizeTodo($prioritizationDto, $todo, $user);
        $todoAccessRepository->prioritizeTodo($todo, $user, $newPriority);

        return $this->json([
            'message' => 'Todo priority changed',
            'todo' => $todo
        ], Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }
}
