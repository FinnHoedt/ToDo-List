<?php

namespace App\Validator;

use App\Repository\CategoryRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CategoryIdExistsForUserValidator extends ConstraintValidator
{

    public function __construct(private readonly Security $security, private readonly CategoryRepository $categoryRepository)
    {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var CategoryIdExistsForUser $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $user = $this->security->getUser();

        if($user === null) {
            throw new \LogicException('The user must be logged in');
        }

        $category = $this->categoryRepository->findOneBy(['id' => $value, 'user' => $user]);

        if($category === null) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
