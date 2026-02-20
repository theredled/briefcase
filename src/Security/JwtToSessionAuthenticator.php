<?php

namespace App\Security;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JwtToSessionAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private UserRepository $userRepository,
    ) {
    }

    // Cet authenticator ne s'active que si le cookie est présent
    public function supports(Request $request): ?bool
    {
        return $request->cookies->has('jwt_token')
            && !$request->getSession()->has('_security_admin'); // pas déjà connecté
    }

    public function authenticate(Request $request): Passport
    {
        $jwt = $request->cookies->get('jwt_token');

        try {
            $payload = $this->jwtManager->parse($jwt);
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid JWT token');
        }

        // UserBadge va charger le user via le provider configuré
        return new SelfValidatingPassport(
            new UserBadge($payload['username'], function (string $identifier) {
                $user = $this->userRepository->findOneBy(['email' => $identifier]);

                if (!$user) {
                    throw new UserNotFoundException();
                }

                if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                    throw new AccessDeniedException();
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // On retourne null → Symfony continue vers la route demandée
        // La session est créée automatiquement par Symfony
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // JWT invalide ou expiré → on redirige vers le login classique
        return new RedirectResponse('/admin/login');
    }
}
