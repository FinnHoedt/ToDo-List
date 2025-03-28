<?php

namespace App\Controller;

use App\Dto\Category\CategoryDto;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Service\Controller\ErrorMessageGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class CategoryController extends AbstractController
{
    #[Route('/api/category', name: 'app_category_index', methods: ['GET'],)]
    public function index(): JsonResponse
    {
        $user = $this->getUser();

        if(!$user instanceof User){
            throw new \LogicException('Authenticated user is not an instance of User');
        }

        $categories = $user->getCategories();

        return $this->json($categories, Response::HTTP_OK, [], ['groups' => ['category:read']]);
    }

    #[Route('/api/category', name: 'app_category_store', methods: ['POST'],)]
    public function store(Request $request,
                          ErrorMessageGenerator $errorMessageGenerator,
                          CategoryRepository $categoryRepository,
                          SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();

        if(!$user instanceof User){
            throw new \LogicException('Authenticated user is not an instance of User');
        }

        $jsonContent = $request->getContent();

        $categoryDto = $serializer->deserialize($jsonContent, CategoryDto::class, 'json');

        $errorMessages = $errorMessageGenerator->generateErrorMessage($categoryDto);
        if($errorMessages !== null){
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $category = $categoryRepository->save($categoryDto, $user);

        return $this->json([
            'message' => 'Category updated',
            'category' => $category
        ], Response::HTTP_OK, [], ['groups' => ['category:read']]);
    }

    #[Route('/api/category/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): JsonResponse
    {
        $user = $this->getUser();

        if(!$user instanceof User){
            throw new \LogicException('Authenticated user is not an instance of User');
        }

        if($category->getUser() !== $user){
            return new JsonResponse(['message' => 'Category does not belong to this user'], Response::HTTP_FORBIDDEN);
        }

        return $this->json($category, Response::HTTP_OK, [], ['groups' => ['category:read']]);
    }

    #[Route('/api/category/{id}', name: 'app_category_edit', methods: ['PATCH'])]
    public function edit(Request $request,
                         Category $category,
                         ErrorMessageGenerator $errorMessageGenerator,
                         CategoryRepository $categoryRepository,
                         SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();

        if(!$user instanceof User){
            throw new \LogicException('Authenticated user is not an instance of User');
        }

        if($category->getUser() !== $user){
            return new JsonResponse(['message' => 'Category does not belong to this user'], Response::HTTP_FORBIDDEN);
        }

        $jsonContent = $request->getContent();

        $categoryDto = $serializer->deserialize($jsonContent, CategoryDto::class, 'json');

        $errorMessages = $errorMessageGenerator->generateErrorMessage($categoryDto);
        if($errorMessages !== null){
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $category = $categoryRepository->edit($categoryDto, $category);

        return $this->json([
            'message' => 'Category updated',
            'category' => $category
        ], Response::HTTP_OK, [], ['groups' => ['category:read']]);
    }

    #[Route('/api/category/{id}', name: 'app_category_destroy', methods: ['DELETE'])]
    public function destroy(Category $category, CategoryRepository $categoryRepository): JsonResponse
    {
        $user = $this->getUser();

        if(!$user instanceof User){
            throw new \LogicException('Authenticated user is not an instance of User');
        }

        if($category->getUser() !== $user){
            return new JsonResponse(['message' => 'Category does not belong to this user'], Response::HTTP_FORBIDDEN);
        }

        $categoryRepository->delete($category);

        return new JsonResponse(['message' => 'Category deleted'], Response::HTTP_OK);
    }
}
