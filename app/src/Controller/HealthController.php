<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends AbstractController
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
