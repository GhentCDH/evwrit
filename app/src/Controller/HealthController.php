<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class HealthController extends BaseController
{

    /**
     * @Route("/health", name="health", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function health(Request $request)
    {
        return new JsonResponse(['status' => 'ok']);
    }

}
