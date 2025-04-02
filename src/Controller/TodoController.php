<?php

namespace App\Controller;

use App\Dto\TodoDto;
use App\Entity\Category;
use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoAccessRepository;
use App\Repository\TodoRepository;
use App\Service\Controller\ErrorMessageGenerator;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

final class TodoController extends AbstractController
{
    #[Route('/api/todo', name: 'app_todo_index', methods: ['GET'])]
    public function index(#[CurrentUser] User $user, TodoRepository $todoRepository): JsonResponse
    {
        $todos = $todoRepository->getTodosOfUserWithCategories($user);

        return $this->json($todos, Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/category/{category_id}',
        name: 'app_todo_by_category',
        requirements: ['category_id' => '\d+'],
        methods: ['GET'])]
    #[IsGranted('CATEGORY_OWNER', 'category')]
    public function getTodosOfSpecificCategory(
        #[CurrentUser] User $user,
        #[MapEntity(mapping: ['category_id' => 'id'])] Category $category,
        TodoRepository $todoRepository): JsonResponse
    {
        $todos = $todoRepository->getTodosOfUserForCategory($user, $category);

        return $this->json($todos, Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/category',
        name: 'app_todo_without_category',
        requirements: ['category_id' => '\d+'],
        methods: ['GET'])]
    public function getTodosWithNoCategory(
        #[CurrentUser] User $user,
        TodoRepository $todoRepository): JsonResponse
    {
        $todos = $todoRepository->getTodosOfUserForCategory($user, null);

        return $this->json($todos, Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/category/{category_id}',
        name: 'app_todo_store_with_category',
        requirements: ['category_id' => '\d+'],
        methods: ['POST'])]
    #[IsGranted('CATEGORY_OWNER', 'category')]
    public function storeWithCategory(
        #[CurrentUser] User $user,
        Request $request,
        #[MapEntity(mapping: ['category_id' => 'id'])] Category $category,
        ErrorMessageGenerator $errorMessageGenerator,
        TodoRepository $todoRepository,
        TodoAccessRepository $todoAccessRepository,
        SerializerInterface $serializer): JsonResponse
    {
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
            'message' => 'Todo created',
            'todo' => $todo,
        ], Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/category', name: 'app_todo_store_without_category', methods: ['POST'])]
    public function storeWithoutCategory(
        #[CurrentUser] User $user,
        Request $request,
        ErrorMessageGenerator $errorMessageGenerator,
        TodoRepository $todoRepository,
        TodoAccessRepository $todoAccessRepository,
        SerializerInterface $serializer): JsonResponse
    {
        $jsonContent = $request->getContent();

        $todoDto = $serializer->deserialize($jsonContent, TodoDto::class, 'json');

        $errorMessages = $errorMessageGenerator->generateErrorMessage($todoDto);
        if($errorMessages !== null){
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $todo = $todoRepository->save($todoDto);
        $todoAccess = $todoAccessRepository->save($todo, $user, null);

        $todo->getTodoAccesses()->add($todoAccess);

        return $this->json([
            'message' => 'Todo created',
            'todo' => $todo,
        ], Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/{todo_id}',
        name: 'app_todo_show',
        requirements: ['todo_id' => '\d+'],
        methods: ['GET'])]
    #[IsGranted('TODO_ACCESS', 'todo')]
    public function show(#[MapEntity(mapping: ['todo_id' => 'id'])] Todo $todo): JsonResponse
    {
        return $this->json($todo, Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }

    #[Route('/api/todo/{todo_id}',
        name: 'app_todo_edit',
        requirements: ['todo_id' => '\d+'],
        methods: ['PATCH'])]
    #[IsGranted('TODO_ACCESS', 'todo')]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['todo_id' => 'id'])] Todo $todo,
        TodoRepository $todoRepository,
        ErrorMessageGenerator $errorMessageGenerator,
        SerializerInterface $serializer): JsonResponse
    {
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


    #[Route('/api/todo/{todo_id}',
        name: 'app_todo_destroy',
        requirements: ['todo_id' => '\d+'],
        methods: ['DELETE'])]
    #[IsGranted('TODO_OWNER', 'todo')]
    public function destroy(#[MapEntity(mapping: ['todo_id' => 'id'])] Todo $todo, TodoRepository $todoRepository): JsonResponse
    {
        $todoRepository->delete($todo);

        return new JsonResponse(['message' => 'Todo deleted'], Response::HTTP_OK);
    }

    #[Route('/api/todo/{todo_id}/complete',
        name: 'app_todo_completed',
        requirements: ['todo_id' => '\d+'],
        methods: ['PATCH'])]
    #[IsGranted('TODO_ACCESS', 'todo')]
    public function toggleCompleted(
        #[MapEntity(mapping: ['todo_id' => 'id'])] Todo $todo, TodoRepository $todoRepository,): JsonResponse
    {
        $todoRepository->toggleCompleted($todo);

        return $this->json([
            'message' => 'Todo updated',
            'todo' => $todo
        ], Response::HTTP_OK, [], ['groups' => ['todo:read', 'todoAccess:read']]);
    }
}
