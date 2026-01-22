<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends BaseController
{

    /**
     * @Route("/health", name="health", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function health(Request $request): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }

}
