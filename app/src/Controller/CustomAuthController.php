<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class CustomAuthController extends AbstractController
{
    #[Route(path: '/user/login', name: 'login', methods: ['GET'])]
    public function login(Request $request, SessionInterface $session): RedirectResponse
    {
        $referer = $request->headers->get('referer', $request->getUri());
        $session->set('pre_login_redirect', $referer);

        return $this->redirectToRoute('idci_keycloak_security_auth_connect');
    }

    #[Route(path: '/user/logout', name: 'logout', methods: ['GET'])]
    public function logout(Request $request, SessionInterface $session): RedirectResponse
    {
        $referer = $request->headers->get('referer', $this->generateUrl('default'));

        $response = $this->redirectToRoute('idci_keycloak_security_auth_logout');
        $response->headers->setCookie(
            Cookie::create('post_logout_redirect')
                ->withValue($referer)
                ->withPath('/')
                ->withSecure(true)
                ->withHttpOnly(true)
                ->withSameSite(Cookie::SAMESITE_LAX)
                ->withExpires(new \DateTime('+5 minutes'))
        );

        return $response;
    }
}