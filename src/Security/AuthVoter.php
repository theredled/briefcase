<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 05/02/2026
 * Time: 17:03
 */

namespace App\Security;


use App\Entity\Briefcase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthVoter extends \Symfony\Component\Security\Core\Authorization\Voter\Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (str_starts_with($attribute, 'briefcase_') and $subject instanceof Briefcase)
            return true;

        return false;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($attribute === 'briefcase_fullaccess')
            return $token && $token->getUser() && $subject->getUser()
                && $subject->getUser()->getId() === $token->getUser()->getId();
        return false;
    }
}
