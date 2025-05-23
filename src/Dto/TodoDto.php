<?php


namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TodoDto
{

    public function __construct(

        #[Assert\NotBlank(message: "Title is required")]
        #[Assert\Length(
            min: 3,
            max: 255,
            minMessage: "Title is minimum 3 characters",
            maxMessage: "Title is maximum 255 characters")]
        #[Assert\Type("string", message: "Title has to be a valid string")]
        public mixed $title,

        #[Assert\Type("string", message: "Description has to be a valid string")]
        public mixed $description,
    )
    {}
}