<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 13/02/2026
 * Time: 06:32
 */

namespace App\DependencyInjection;


use App\Security\AuthVoter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class AuthCheckerInjectionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('app.needs_auth_checker') as $serviceId => $tags) {
            $container->getDefinition($serviceId)->addMethodCall('setAuthChecker', [
                $container->getDefinition('security.authorization_checker')
            ]);
        }
    }
}
