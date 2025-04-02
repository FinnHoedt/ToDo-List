<?php

namespace App\Security\Voter;

use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class TodoVoter extends Voter
{
    public const OWNER = 'TODO_OWNER';
    public const ACCESS = 'TODO_ACCESS';

    public function __construct(private readonly TodoAccessRepository $todoAccessRepository)
    {}


    protected function supports(string $attribute, mixed $subject): bool
    {
        if(!in_array($attribute, [self::OWNER, self::ACCESS]))
            return false;

        if(!$subject instanceof Todo)
            return false;

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $todo = $subject;

        return match ($attribute) {
            self::ACCESS => $this->ensureUserHasAccess($todo, $user),
            self::OWNER => $this->ensureUserIsOwner($todo, $user),
            default => false,
        };
    }

    private function ensureUserIsOwner(Todo $todo, User $user): bool
    {
        $todoAccess = $this->todoAccessRepository->getTodoAccessOfTodoForUser($todo, $user);

        if($todoAccess === null)
            return false;

        if($todoAccess->isShared())
            return false;

        return true;
    }

    private function ensureUserHasAccess(Todo $todo, User $user): bool
    {
        if ($this->todoAccessRepository->getTodoAccessOfTodoForUser($todo, $user) === null)
            return false;

        return true;
    }
}
