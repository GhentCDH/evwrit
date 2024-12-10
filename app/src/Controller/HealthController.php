<?php

namespace App\Controller;

use App\Helper\StreamedCsvResponse;
use App\Repository\TextRepository;
use App\Resource\ElasticTextAnnotationsResource;
use App\Resource\TextSearchFlagsResource;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class HealthController extends BaseController
{

    /**
     * @Route("/health", name="health", methods={"GET"})
     * @param Request $request
     * @return RedirectResponse
     */
    public function health(Request $request)
    {
        return new JsonResponse(['status' => 'ok']);
    }

}
