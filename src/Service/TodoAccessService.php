<?php

namespace App\Service;

use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoAccessRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class TodoAccessService
{
    public function __construct(private TodoAccessRepository $todoAccessRepository)
    {}


    /**
     * Ensures the user has access to the given todo.
     *
     * @throws NotFoundHttpException if the user does not have access.
     */
    public function ensureUserHasAccess(Todo $todo, User $user): void
    {
        if ($this->todoAccessRepository->getTodoAccessOfTodoForUser($todo, $user) === null) {
            throw new NotFoundHttpException('User does not have access to this todo.');
        }
    }

}