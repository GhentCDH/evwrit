<?php

namespace App\Controller;

use App\Helper\StreamedCsvResponse;
use App\Service\ElasticSearch\TextBasicSearchService;
use App\Service\ElasticSearch\TextMaterialitySearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MaterialityController extends BaseController
{
    protected $templateFolder = 'Materiality';

    protected const searchServiceName = "text_materiality_search_service";
    protected const indexServiceName = "text_index_service";

    /**
     * @Route("/materiality", name="materiality", methods={"GET"})
     * @param Request $request
     * @return RedirectResponse
     */
    public function index(Request $request)
    {
        return $this->redirectToRoute('materiality_search', ['request' =>  $request], 301);
    }

    /**
     * @Route("/materiality/search", name="materiality_search", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function search(
        Request $request
    ) {
        return $this->_search(
            $request,
            [
                'title' => 'Materiality'
            ],
            [
                'search_api' => 'materiality_search_api',
                'paginate' => 'materiality_paginate',
                'export_csv' => 'materiality_export_csv'
            ]
        );
    }

    /**
     * @Route("/materiality/search_api", name="materiality_search_api", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function search_api(
        Request $request
    ) {
        return $this->_search_api($request);
    }

    /**
     * @Route("/materiality/paginate", name="materiality_paginate", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function paginate(
        Request $request
    ) {
        return $this->_paginate($request);
    }

    /**
     * @Route("/materiality/export/csv", name="materiality_export_csv", methods={"GET"})
     * @param Request $request
     * @param TextMaterialitySearchService $elasticService
     * @return StreamedCsvResponse
     */
    public function exportCSV(
        Request $request,
        TextMaterialitySearchService $elasticService
    ) {
        $elasticService = $this->getContainer()->get(static::searchServiceName);

        // search
        $data = $elasticService->searchRAW(
            $this->sanitizeSearchRequest($request->query->all())
        );

        // header
        $csvHeader = ['id', 'tm_id', 'year_begin', 'year_end', 'text'];

        // data
        $csvData = [];
        $csvData[] = $csvHeader;
        foreach ($data['data'] as $row) {
            $csvRow = [];

            $csvRow[] = $row['id'];
            $csvRow[] = $row['tm_id'];
            $csvRow[] = $row['year_begin'];
            $csvRow[] = $row['year_end'];
            $csvRow[] = $row['text'];

            $csvData[] = $csvRow;
        }

        // csv response
        $response = new StreamedCsvResponse($csvData, $csvData[0], 'materiality.csv');
        return $response;
    }


}
