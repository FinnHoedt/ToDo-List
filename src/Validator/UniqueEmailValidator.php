<?php

namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueEmailValidator extends ConstraintValidator
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var UniqueEmail $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $existingUser = $this->userRepository->findOneBy(['email' => $value]);
        if (null === $existingUser) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $value)
            ->addViolation()
        ;
    }
}
