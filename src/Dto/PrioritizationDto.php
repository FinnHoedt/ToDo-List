<?php

namespace App\Dto;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as AppAssert;

#[AppAssert\PrioritizationRequestAtLeastOneField]
class PrioritizationDto
{
    public function __construct(
        #[Assert\Type('integer')]
        #[AppAssert\TodoIdExistsForUser]
        public mixed $beforeId,

        #[Assert\Type('integer')]
        #[AppAssert\TodoIdExistsForUser]
        public mixed $afterId,
    ){}
}