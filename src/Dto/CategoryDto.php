<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CategoryDto
{

    public function __construct(
        #[Assert\NotBlank(message: "Title is required")]
        #[Assert\Type('string')]
        #[Assert\Length(
            min: 3,
            max: 255,
            minMessage: "Title is minimum 3 characters",
            maxMessage: "Title is maximum 255 characters")]
        public mixed $title,
    ){}
}