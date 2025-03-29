<?php

namespace App\Controller;

use App\Dto\TodoDto;
use App\Entity\Category;
use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoAccessRepository;
use App\Repository\TodoRepository;
use App\Service\Controller\ErrorMessageGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

final class TodoController extends AbstractController
{
    #[Route('/api/todo', name: 'app_todo_index', methods: ['GET'])]
    public function index(#[CurrentUser] User $user,
                          TodoRepository $todoRepository): JsonResponse
    {
        $todos = $todoRepository->getTodosOfUser($user)->toArray();

        return $this->json($todos, Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/category/{todo_id}', name: 'app_todo_by_category', methods: ['GET'])]
    public function getTodosOfSpecificCategory(#[CurrentUser] User $user,
                                               Category $category,
                                               TodoRepository $todoRepository): JsonResponse
    {
        if($category->getUser() !== $user){
            throw $this->createNotFoundException();
        }

        $todos = $todoRepository->getTodosOfUserOfSpecificCategory($user, $category);

        return $this->json($todos, Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/category/{category_id}', name: 'app_todo_store', methods: ['POST'])]
    public function store(#[CurrentUser] User $user,
                          Request $request,
                          Category $category,
                          ErrorMessageGenerator $errorMessageGenerator,
                          TodoRepository $todoRepository,
                          TodoAccessRepository $todoAccessRepository,
                          SerializerInterface $serializer): JsonResponse
    {
        if($category->getUser() !== $user){
            throw $this->createNotFoundException();
        }

        $jsonContent = $request->getContent();

        $todoDto = $serializer->deserialize($jsonContent, TodoDto::class, 'json');

        $errorMessages = $errorMessageGenerator->generateErrorMessage($todoDto);
        if($errorMessages !== null){
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $todo = $todoRepository->save($todoDto);
        $todoAccess = $todoAccessRepository->save($todo, $user, $category);

        $todo->getTodoAccesses()->add($todoAccess);

        return $this->json([
            'message' => 'Todo updated',
            'todo' => $todo,
        ], Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/{todo_id}', name: 'app_todo_show', methods: ['GET'])]
    public function show(Todo $todo, TodoAccessRepository $todoAccessRepository): JsonResponse
    {
        $user = $this->getUser();

        if(!$user instanceof User){
            throw new \LogicException('Authenticated user is not an instance of User');
        }

        if(!$todoAccessRepository->doesUserHaveAccessToTodo($todo,$user)){
            throw $this->createNotFoundException();
        }

        return $this->json($todo, Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/{todo_id}', name: 'app_todo_edit', methods: ['PATCH'])]
    public function edit(#[CurrentUser] User $user,
                         Request $request,
                         Todo $todo,
                         TodoRepository $todoRepository,
                         TodoAccessRepository $todoAccessRepository,
                         ErrorMessageGenerator $errorMessageGenerator,
                         SerializerInterface $serializer): JsonResponse
    {
        if(!$todoAccessRepository->doesUserHaveAccessToTodo($todo,$user)){
            throw $this->createNotFoundException();
        }

        $jsonContent = $request->getContent();

        $todoDto = $serializer->deserialize($jsonContent, TodoDto::class, 'json');

        $errorMessages = $errorMessageGenerator->generateErrorMessage($todoDto);
        if($errorMessages !== null){
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $todo = $todoRepository->edit($todoDto, $todo);

        return $this->json([
            'message' => 'Todo updated',
            'todo' => $todo
        ], Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }


    #[Route('/api/todo/{todo_id}', name: 'app_todo_destroy', methods: ['DELETE'])]
    public function destroy(#[CurrentUser] User $user,
                            Todo $todo,
                            TodoRepository $todoRepository,
                            TodoAccessRepository $todoAccessRepository): JsonResponse
    {
        if(!$todoAccessRepository->doesUserHaveAccessToTodo($todo,$user)){
            throw $this->createNotFoundException();
        }

        $todoRepository->delete($todo);

        return new JsonResponse(['message' => 'Todo deleted'], Response::HTTP_OK);
    }

    #[Route('/api/todo/{todo_id}/completed', name: 'app_todo_completed', methods: ['PATCH'])]
    public function toggleCompleted(#[CurrentUser] User $user, Todo $todo, TodoRepository $todoRepository, TodoAccessRepository $todoAccessRepository): JsonResponse
    {
        if(!$todoAccessRepository->doesUserHaveAccessToTodo($todo,$user)){
            throw $this->createNotFoundException();
        }

        $todoRepository->toggleCompleted($todo);

        return $this->json([
            'message' => 'Todo updated',
            'todo' => $todo
        ], Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/{todo_id}/user/{user_id}', name: 'app_todo_completed', methods: ['POST'])]
    public function shareTodo(#[CurrentUser] User $user,
                              Todo $todo,
                              User $userToGetShared,
                              TodoAccessRepository $todoAccessRepository): JsonResponse
    {
        $user = $this->getUser();

        if(!$user instanceof User){
            throw new \LogicException('Authenticated user is not an instance of User');
        }

        if(!$todoAccessRepository->doesUserHaveAccessToTodo($todo,$user)){
            throw $this->createNotFoundException();
        }

        $todoAccessRepository->shareTodo($userToGetShared);

        return $this->json([
            'message' => 'Todo updated',
            'todo' => $todo
        ], Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }
}
