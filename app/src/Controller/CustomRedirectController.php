<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CustomRedirectController extends BaseController
{
    #[Route(path: '/post_auth_redirect', name: 'post_auth_redirect', methods: ['GET'])]
    public function postLoginRedirect(Request $request, SessionInterface $session): RedirectResponse
    {
        if ($session->has('pre_login_redirect')) {
            $redirectUrl = $session->remove('pre_login_redirect');
            return $this->redirect($redirectUrl);
        }

        if ($request->cookies->has('post_logout_redirect')) {
            $redirectUrl = $request->cookies->get('post_logout_redirect');
            return $this->redirect($redirectUrl);
        }

        $redirectUrl = $this->generateUrl('default');
        return $this->redirect($redirectUrl);
    }
}