<?php

// src/AppBundle/Controller/SecurityController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route("/login", name: "login")]
    public function loginAction(AuthenticationUtils $authenticationUtils, Request $request)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('@EasyAdmin/page/login.html.twig', [
            // parameters usually defined in Symfony login forms
            'error' => $error,
            'last_username' => $lastUsername,
            'target_path' => $request->query->get('redirect', $this->generateUrl('admin'))
        ]);
    }

    /**
     * @throws \RuntimeException
     */
    #[Route("/logout", name: "logout")]
    public function logoutAction()
    {
        throw new \RuntimeException('This should never be called directly.');
    }
}