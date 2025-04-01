<?php

namespace App\Dto;
use Symfony\Component\Validator\Constraints as Assert;

class PrioritizationDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\PositiveOrZero]
        public mixed $position,
    ){}
}