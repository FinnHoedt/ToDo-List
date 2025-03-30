<?php

namespace App\Validator;

use App\Dto\PrioritizationDto;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class PrioritizationRequestAtLeastOneFieldValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var PrioritizationRequestAtLeastOneField $constraint */

        if (!$value instanceof PrioritizationDto) {
            throw new UnexpectedTypeException($value, PrioritizationDto::class);
        }

        if (null === $value->beforeId && null === $value->afterId) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
