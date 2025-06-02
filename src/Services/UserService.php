<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 02/06/2025
 * Time: 23:56
 */

namespace App\Services;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserService
{
    public function __construct(
        protected TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function getCurrentUser()
    {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface) {

            /** @var User $user */
            $user = $token->getUser();
            return $user;

        } else {
            return null;
        }
    }
}