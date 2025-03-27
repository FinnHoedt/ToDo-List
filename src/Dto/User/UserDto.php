<?php

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as AppAssert;

readonly class UserDto
{
    public function __construct(
        #[Assert\NotBlank(message: "Email is required")]
        #[Assert\Email(message: 'Please provide a valid email address')]
        #[Assert\Length(max: 180, maxMessage: "Email is maximum 180 characters")]
        #[AppAssert\UniqueEmail]
        public ?string $email,

        #[Assert\NotBlank(message: "Password is required")]
        #[Assert\Length(min: 8, minMessage: "Password must be at least 8 characters long")]
        public ?string $password
    ){}

}