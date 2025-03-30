<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class PrioritizationRequestAtLeastOneField extends Constraint
{
    public string $message = 'Either beforeId or afterId has to be set';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
