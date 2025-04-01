<?php

namespace App\Security\Voter;

use App\Entity\Category;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CategoryVoter extends Voter
{
    public const OWNER = 'CATEGORY_OWNER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if($attribute !== self::OWNER)
            return false;

        if(!$subject instanceof Category)
            return false;

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $category = $subject;

        return $category->getUser() === $user;
    }
}
