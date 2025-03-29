<?php

namespace App\Dto;

use App\Validator as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UserDto
{
    public function __construct(
        #[Assert\NotBlank(message: "Email is required")]
        #[Assert\Type('string')]
        #[Assert\Email(message: 'Please provide a valid email address')]
        #[Assert\Length(max: 180, maxMessage: "Email is maximum 180 characters")]
        #[AppAssert\UniqueEmail]
        public mixed $email,

        #[Assert\NotBlank(message: "Password is required")]
        #[Assert\Type('string')]
        #[Assert\Length(min: 8, minMessage: "Password must be at least 8 characters long")]
        public mixed $password
    ){}

}