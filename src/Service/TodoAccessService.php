<?php

namespace App\Service;

use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoAccessRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

readonly class TodoAccessService
{
    public function __construct(private TodoAccessRepository $todoAccessRepository)
    {}


    /**
     * Ensures the user has access to the given todo.
     *
     * @throws AccessDeniedException if the user does not have access.
     */
    public function ensureUserHasAccess(Todo $todo, User $user): void
    {
        if ($this->todoAccessRepository->getTodoAccessOfTodoForUser($todo, $user) === null) {
            throw new AccessDeniedException('User does not have access to this todo.');
        }
    }

    /**
     * Ensures the user is owner of the given todo.
     *
     * @throws AccessDeniedException if the user is not the owner.
     */
    public function ensureUserIsOwner(Todo $todo, User $user): void
    {
        $todoAccess = $this->todoAccessRepository->getTodoAccessOfTodoForUser($todo, $user);

        if($todoAccess->isShared())
        {
            throw new AccessDeniedException('You do not have permission to do this.');
        }
    }

}