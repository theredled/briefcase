<?php
/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 13/02/2026
 * Time: 06:39
 */

namespace App\DependencyInjection;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

interface HasAuthChecker
{
    //public function getAuthChecker();
    public function setAuthChecker(AuthorizationChecker $authChecker);
}