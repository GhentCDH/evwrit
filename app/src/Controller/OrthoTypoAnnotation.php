<?php

namespace App\Controller;

use App\Helper\StreamedCsvResponse;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;


class OrthoTypoAnnotation extends BaseController
{
    protected $templateFolder = 'BaseAnnotation';

    protected const searchServiceName = "orthotypo_annotation_search_service";
    protected const indexServiceName = "text_index_service";

    /**
     * @Route("/annotation", name="annotation", methods={"GET"})
     * @param Request $request
     * @return RedirectResponse
     */
    public function index(Request $request)
    {
        return $this->redirectToRoute('orthotypo_annotation_search', ['request' =>  $request], 301);
    }

    /**
     * @Route("/annotation/orthotypo/search", name="orthotypo_annotation_search", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function search(
        Request $request
    ) {
        return $this->_search(
            $request,
            [
                'title' => 'Orthograpy and Typography',
                'defaultAnnotationType' => null
            ],
            [
                'search_api' => 'orthotypo_annotation_search_api',
                'paginate' => 'orthotypo_annotation_paginate',
                'export_csv' => 'orthotypo_annotation_export_csv'
            ]
        );
    }

    /**
     * @Route("/annotation/orthotypo/search_api", name="orthotypo_annotation_search_api", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function search_api(Request $request): JsonResponse {
        return $this->_search_api($request);
    }

    /**
     * @Route("/annotation/orthotypo/paginate", name="orthotypo_annotation_paginate", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function paginate(Request $request): JsonResponse {
        return $this->_paginate($request);
    }

    /**
     * @Route("/annotation/orthotypo/export/csv", name="orthotypo_annotation_export_csv", methods={"GET"})
     * @param Request $request
     * @return StreamedCsvResponse
     */
    public function exportCSV(
        Request $request
    ) {
        $elasticService = $this->getContainer()->get(static::searchServiceName);

        // search
        $data = $elasticService->searchRAW(
            $this->sanitizeSearchRequest($request->query->all()),
            ['id', 'tm_id', 'annotations']
        );

        $csvData = $this->renderCsvData($data);

        // csv response
        $response = new StreamedCsvResponse($csvData, $csvData[0], 'orthotypo-annotations.csv');
        return $response;
    }

    /**
     * @param array $data
     * @return string[][]
     */
    private function renderCsvData(array $data): array {
        // header
        $csvHeader = ['text_id', 'tm_id', 'annotation_id', 'annotation_type', 'annotation_text', 'annotation_text_edited'];
        $csvAnnotationProperties = [];

        // data
        $csvData = [ $csvHeader ];
        foreach ($data['data'] as $text) {

            foreach($text['inner_hits']['annotations'] ?? $text['annotations'] ?? [] as $annotation) {

                $csvRow = [];

                // text data
                $csvRow['text_id'] = $text['id'];
                $csvRow['tm_id'] = $text['tm_id'];

                // annotation data
                $csvRow['annotation_id'] = $annotation['id'];
                $csvRow['annotation_type'] = $annotation['type'];

                // text selection
                $csvRow['annotation_text'] = $annotation['text_selection']['text'];
                $csvRow['annotation_text_edited'] = $annotation['text_selection']['text_edited'];

                // annotation properties
                foreach($annotation['properties'] as $property => $propertyValue) {
                    if ( $propertyValue && isset($propertyValue['name']) ) {
                        $csvRow[$property] = is_array($propertyValue['name']) ? implode(';', array_unique($propertyValue['name'])) : $propertyValue['name'];
                        $csvAnnotationProperties[$property] = $property;
                    }
                }

                // text structure

                $csvData[] = $csvRow;
            }
        }

        ksort($csvAnnotationProperties);
        $csvHeader += array_values($csvAnnotationProperties);

        $csvData[0] = $csvHeader;

        return $csvData;
    }
}
