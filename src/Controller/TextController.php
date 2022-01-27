<?php

namespace App\Controller;

use App\Helper\StreamedCsvResponse;
use App\Model\Text;
use App\Repository\LanguageAnnotationRepository;
use App\Resource\BaseAnnotationResource;
use App\Resource\BaseElasticAnnotationResource;
use App\Resource\ElasticLanguageAnnotationResource;
use App\Resource\ElasticTextResource;
use App\Resource\ElasticTypographyAnnotationResource;
use App\Resource\TextResource;
use App\Service\ElasticSearch\AbstractSearchService;
use App\Service\ElasticSearch\TextBasicSearchService;
use App\Service\ElasticSearch\TextIndexService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\TextRepository;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

class TextController extends BaseController
{
    protected $templateFolder = 'Text';

    protected const searchServiceName = "text_basic_search_service";
    protected const indexServiceName = "text_index_service";

    /**
     * @Route("/text", name="text", methods={"GET"})
     * @param Request $request
     * @return RedirectResponse
     */
    public function index(Request $request)
    {
        return $this->redirectToRoute('text_search', ['request' =>  $request], 301);
    }

    /**
     * @Route("/text/search", name="text_search", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function search(
        Request $request
    ) {
        return $this->_search(
            $request,
            [
                'title' => 'Texts'
            ],
            [
                'search_api' => 'text_search_api',
                'paginate' => 'text_paginate',
                'export_csv' => 'text_export_csv'
            ]
        );
    }

    /**
     * @Route("/text/search_api", name="text_search_api", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function search_api(
        Request $request
    ) {
        return $this->_search_api($request);
    }

    /**
     * @Route("/text/paginate", name="text_paginate", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function paginate(
        Request $request
    ) {
        return $this->_paginate($request);
    }

    /**
     * @Route("/text/export/csv", name="text_export_csv", methods={"GET"})
     * @param Request $request
     * @return StreamedCsvResponse
     */
    public function exportCSV(
        Request $request
    ) {
        $elasticService = $this->getContainer()->get(static::searchServiceName);

        $header = [];

        // search
        $data = $elasticService->searchRAW(
            $this->sanitizeSearchRequest($request->query->all())
        );

        // header
        $csvHeader = ['id', 'tm_id', 'year_begin', 'year_end', 'text'];

        // data
        $csvData = [];
        foreach ($data['data'] as $row) {
            $csvRow = [];

            $csvRow['id'] = $row['id'];
            $csvRow['tm_id'] = $row['tm_id'];
            $csvRow['year_begin'] = $row['year_begin'];
            $csvRow['year_end'] = $row['year_end'];
            $csvRow['text'] = $row['text'];

            $csvData[] = $csvRow;
        }

        // csv response
        $response = new StreamedCsvResponse($csvData, $csvHeader, 'texts.csv');
        return $response;
    }


    /**
     * @Route("/text/{id}", name="text_get_single", methods={"GET"})
     * @param int $id
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function getSingle(int $id, Request $request)
    {
        $elasticService = $this->getContainer()->get(self::indexServiceName);

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            //$this->denyAccessUnlessGranted('ROLE_EDITOR_VIEW');
            try {
                $resource = $elasticService->get($id);
            } catch (NotFoundHttpException $e) {
                return new JsonResponse(
                    ['error' => ['code' => Response::HTTP_NOT_FOUND, 'message' => $e->getMessage()]],
                    Response::HTTP_NOT_FOUND
                );
            }
            return new JsonResponse($resource);
        } else {
            // Let the 404 page handle the not found exception
            $resource = $elasticService->get($id);

            return $this->render(
                $this->templateFolder. '/detail.html.twig',
                [
                    'urls' => json_encode($this->getSharedAppUrls()),
                    'data' => json_encode([
                        'text' => $resource
                    ])
                ]
            );
        }
    }

}
