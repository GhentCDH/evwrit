<?php

namespace App\Security\EntryPoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Twig\Environment;

class CustomAuthenticationEntryPoint implements \Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface
{
    protected string $templateFolder = 'Security';

    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        // For API requests, return JSON error
        if (str_starts_with($request->getPathInfo(), '/api/')) {
            return new Response(
                json_encode([
                    'error' => [
                        'code' => Response::HTTP_UNAUTHORIZED,
                        'message' => 'Authentication required. Please provide a valid Bearer token.'
                    ]
                ]),
                Response::HTTP_UNAUTHORIZED,
                ['Content-Type' => 'application/json']
            );
        }

        // For web requests, show access denied message instead of redirect
        $html = $this->twig->render(
            $this->templateFolder. '/403.html.twig'
        );

        return new Response($html, Response::HTTP_FORBIDDEN);
    }
}

