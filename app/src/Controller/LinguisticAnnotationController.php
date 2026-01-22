<?php

namespace App\Controller;

use App\Helper\StreamedCsvResponse;
use App\Service\ElasticSearch\Search\LexicogrammerAnnotationSearchService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinguisticAnnotationController extends BaseController
{
    protected string $templateFolder = 'BaseAnnotation';

    protected const searchServiceName = "linguistic_annotation_search_service";
    protected const indexServiceName = "text_index_service";

    /**
     * @Route("/annotation/linguistic/search", name="linguistic_annotation_search", methods={"GET"})
     * @param Request $request
     * @param LexicogrammerAnnotationSearchService $elasticService
     * @return Response
     */
    public function search(
        Request $request
    ): Response
    {
        return $this->_search(
            $request,
            [
                'title' => 'Lexicogrammar',
                'defaultAnnotationType' => null
            ],
            [
                'search_api' => 'linguistic_annotation_search_api',
                'paginate' => 'linguistic_annotation_paginate',
                'export_csv' => 'linguistic_annotation_export_csv'
            ]
        );
    }

    /**
     * @Route("/annotation/linguistic/search_api", name="linguistic_annotation_search_api", methods={"GET"})
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
     * @Route("/annotation/linguistic/paginate", name="linguistic_annotation_paginate", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function paginate(
        Request $request
    ): JsonResponse
    {
        return $this->_paginate($request);
    }


    /**
     * @Route("/annotation/linguistic/export/csv", name="linguistic_annotation_export_csv", methods={"GET"})
     * @param Request $request
     * @return StreamedCsvResponse
     */
    public function exportCSV(
        Request $request
    ): StreamedCsvResponse
    {
        $elasticService = $this->getContainer()->get(static::searchServiceName);

        // search
        $data = $elasticService->searchRAW(
            $this->sanitizeSearchRequest($request->query->all()),
            ['id', 'tm_id', 'annotations']
        );

        $csvData = $this->renderCsvData($data);

        // csv response
        $response = new StreamedCsvResponse($csvData, $csvData[0], 'lexicogrammar.csv');
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
