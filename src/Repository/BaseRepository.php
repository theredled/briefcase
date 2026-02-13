<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 13/02/2026
 * Time: 06:24
 */

namespace App\Repository;


use App\DependencyInjection\HasAuthChecker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class BaseRepository extends ServiceEntityRepository implements HasAuthChecker
{
    protected AuthorizationChecker $authChecker;

    protected function getAuthChecker(): AuthorizationChecker
    {
        return $this->authChecker;
    }

    public function setAuthChecker(AuthorizationChecker $authChecker)
    {
        $this->authChecker = $authChecker;
    }
}
