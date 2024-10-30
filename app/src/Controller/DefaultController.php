<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    protected $templateFolder = 'Default';

    /**
     * @Route("/", name="default", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        return $this->render(
            $this->templateFolder. '/index.html.twig');
    }

}
