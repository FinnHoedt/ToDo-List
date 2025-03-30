<?php

namespace App\Validator;

use App\Entity\User;
use App\Repository\TodoAccessRepository;
use App\Repository\TodoRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class TodoIdExistsForUserValidator extends ConstraintValidator
{

    public function __construct(
        private readonly TodoAccessRepository $todoAccessRepository,
        private  readonly TodoRepository $todoRepository,
        private readonly Security $security)
    {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var TodoIdExistsForUser $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $user = $this->security->getUser();

        if ($user === null) {
            throw new \LogicException('The user must be logged in');
        }

        if (!$user instanceof User) {
            throw new \LogicException('User is not of the expected type');
        }

        $todo = $this->todoRepository->find($value);

        if($todo === null)
        {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
            return;
        }

        if($this->todoAccessRepository->getTodoAccessOfTodoForUser($todo, $user) === null)
        {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
