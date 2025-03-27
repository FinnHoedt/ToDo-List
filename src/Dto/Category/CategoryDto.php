<?php

namespace App\Dto\Category;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CategoryDto
{

    public function __construct(
        #[Assert\NotBlank(message: "Title is required")]
        #[Assert\Length(
            min: 3,
            max: 255,
            minMessage: "Title is minimum 3 characters",
            maxMessage: "Title is maximum 255 characters")]
        public ?string $title,
    ){}
}