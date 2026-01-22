<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TextStructureController extends BaseController
{
    protected string $templateFolder = 'TextStructure';

    protected const searchServiceName = "text_structure_search_service";
    protected const indexServiceName = "text_index_service";

    /**
     * @Route("/textstructure/search", name="text_structure_search", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function search(
        Request $request
    ): Response
    {
        return $this->_search(
            $request,
            [
                'title' => 'Text structure'
            ],
            [
                'search_api' => 'text_structure_search_api',
                'paginate' => 'text_structure_paginate',
                'export_csv' => 'text_structure_export_csv'
            ]
        );
    }

    /**
     * @Route("/textstructure/search_api", name="text_structure_search_api", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function search_api(
        Request $request
    ): JsonResponse
    {
        return $this->_search_api($request);
    }

    /**
     * @Route("/textstructure/paginate", name="text_structure_paginate", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function paginate(
        Request $request
    ): JsonResponse
    {
        return $this->_paginate($request, 'text_id');
    }

}
