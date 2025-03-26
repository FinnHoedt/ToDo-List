<?php

namespace App\Service\Controller;

use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ErrorMessageGenerator
{
    public function __construct(private ValidatorInterface $validator){}
    public function generateErrorMessage($dto): array | null {

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = [
                    'property' => $error->getPropertyPath(),
                    'message' => $error->getMessage()
                ];
            }
            return $errorMessages;
        }
        return null;
    }
}