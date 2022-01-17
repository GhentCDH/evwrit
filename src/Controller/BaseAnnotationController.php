<?php

namespace App\Controller;

use App\Helper\StreamedCsvResponse;
use App\Service\ElasticSearch\AnnotationSearchService;

use App\Service\ElasticSearch\LanguageTypographyAnnotationSearchService;
use App\Service\ElasticSearch\LinguisticAnnotationSearchService;
use App\Service\ElasticSearch\TextBasicSearchService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BaseAnnotationController extends BaseController
{
    protected $templateFolder = 'BaseAnnotation';


    /**
     * @Route("/annotation", name="annotation", methods={"GET"})
     * @param Request $request
     * @return RedirectResponse
     */
    public function index(Request $request)
    {
        return $this->redirectToRoute('annotation_search', ['request' =>  $request], 301);
    }

    /**
     * @Route("/annotation/linguistic/search", name="annotation_linguistic_search", methods={"GET"})
     * @param Request $request
     * @param LinguisticAnnotationSearchService $elasticService
     * @return Response
     */
    public function linguisticSearch(
        Request $request,
        LinguisticAnnotationSearchService $elasticService
    ) {
        // update elastic service config
        $data = $elasticService->searchAndAggregate(
            $this->sanitizeSearchRequest($request->query->all())
        );

        dump($data['data'][0]);
        $csvData = $this->renderCsvData($data);
        dump($csvData);

        return $this->render(
            $this->templateFolder. '/overview.html.twig',
            [
                'title' => 'Linguistic annotations',
                'urls' => json_encode([
                    // @codingStandardsIgnoreStart Generic.Files.LineLength
                    'search_api' => $this->generateUrl('annotation_linguistic_search_api'),
                    'get_single' => $this->generateUrl('text_get_single', ['id' => 'text_id']),
                    'export_csv' => $this->generateUrl('annotation_linguistic_export_csv'),
                    // @codingStandardsIgnoreEnd
                ]),
                'data' => json_encode($data),
                'identifiers' => json_encode([]),
                'managements' => json_encode([])
            ]
        );
    }

    /**
     * @Route("/annotation/linguistic/search_api", name="annotation_linguistic_search_api", methods={"GET"})
     * @param Request $request
     * @param LinguisticAnnotationSearchService $elasticService
     * @return JsonResponse
     */
    public function linguisticSearchAPI(
        Request $request,
        LinguisticAnnotationSearchService $elasticService
    ) {
        // update elastic service config

        // search & aggregate
        $result = $elasticService->searchAndAggregate(
            $this->sanitizeSearchRequest($request->query->all())
        );

        return new JsonResponse($result);
    }

    /**
     * @Route("/annotation/languagetypography/search", name="annotation_languagetypography_search", methods={"GET"})
     * @param Request $request
     * @param LanguageTypographyAnnotationSearchService $elasticService
     * @return Response
     */
    public function languageTypographySearch(
        Request $request,
        LanguageTypographyAnnotationSearchService $elasticService
    ) {
        // update elastic service config


        return $this->render(
            $this->templateFolder. '/overview.html.twig',
            [
                'title' => 'Languages and Typography',
                'urls' => json_encode([
                    // @codingStandardsIgnoreStart Generic.Files.LineLength
                    'search_api' => $this->generateUrl('annotation_languagetypography_search_api'),
                    'get_single' => $this->generateUrl('text_get_single', ['id' => 'text_id']),
                    'export_csv' => $this->generateUrl('annotation_languagetypography_export_csv'),
                    // @codingStandardsIgnoreEnd
                ]),
                'data' => json_encode(
                    $elasticService->searchAndAggregate(
                        $this->sanitizeSearchRequest($request->query->all())
                    )
                ),
                'identifiers' => json_encode([]),
                'managements' => json_encode([])
            ]
        );
    }

    /**
     * @Route("/annotation/languagetypography/search_api", name="annotation_languagetypography_search_api", methods={"GET"})
     * @param Request $request
     * @param LanguageTypographyAnnotationSearchService $elasticService
     * @return JsonResponse
     */
    public function languageTypographySearchAPI(
        Request $request,
        LanguageTypographyAnnotationSearchService $elasticService
    ) {
        // update elastic service config

        // search & aggregate
        $result = $elasticService->searchAndAggregate(
            $this->sanitizeSearchRequest($request->query->all())
        );

        return new JsonResponse($result);
    }

    /**
     * @Route("/annotation/languagetypography/export/csv", name="annotation_languagetypography_export_csv", methods={"GET"})
     * @param Request $request
     * @param LanguageTypographyAnnotationSearchService $elasticService
     * @return StreamedCsvResponse
     */
    public function languageTypographyExportCSV(
        Request $request,
        LanguageTypographyAnnotationSearchService $elasticService
    ) {
        // search
        $data = $elasticService->searchRAW(
            $this->sanitizeSearchRequest($request->query->all()),
            ['id', 'tm_id', 'annotations']
        );

        $csvData = $this->renderCsvData($data);

        // csv response
        $response = new StreamedCsvResponse($csvData, $csvData[0], 'languagetypography-annotations.csv');
        return $response;
    }

    /**
     * @Route("/annotation/linguistic/export/csv", name="annotation_linguistic_export_csv", methods={"GET"})
     * @param Request $request
     * @param LanguageTypographyAnnotationSearchService $elasticService
     * @return StreamedCsvResponse
     */
    public function linguisticExportCSV(
        Request $request,
        LanguageTypographyAnnotationSearchService $elasticService
    ) {
        // search
        $data = $elasticService->searchRAW(
            $this->sanitizeSearchRequest($request->query->all()),
            ['id', 'tm_id', 'annotations']
        );

        $csvData = $this->renderCsvData($data);

        // csv response
        $response = new StreamedCsvResponse($csvData, $csvData[0], 'linguistic-annotations.csv');
        return $response;
    }

    /**
     * Sanitize data from request string
     * @param array $params
     * @return array
     */
    private function sanitizeSearchRequest(array $params): array
    {
        return $params;
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
