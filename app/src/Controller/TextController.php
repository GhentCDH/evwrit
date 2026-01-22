<?php

namespace App\Controller;

use App\Helper\StreamedCsvResponse;
use App\Model\AbstractAnnotationModel;
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

class TextController extends BaseController
{
    protected string $templateFolder = 'Text';

    protected const searchServiceName = "text_basic_search_service";
    protected const indexServiceName = "text_index_service";
    protected const searchFlagServiceName = "text_search_flags_service";

    /**
     * @Route("/text", name="text", methods={"GET"})
     * @param Request $request
     * @return RedirectResponse
     */
    public function index(Request $request): RedirectResponse
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
    ): Response
    {
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
    ): JsonResponse
    {
        return $this->_search_api($request);
    }

    /**
     * @Route("/text/search_flags/filters", name="text_search_flags_filters", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function search_flags_filters(
        Request $request
    ): JsonResponse
    {
        $search_service = $this->getContainer()->get(static::searchFlagServiceName);
        $data = $search_service->filters($request);

        return new JsonResponse($data);
    }

    /**
     * @Route("/text/search_flags", name="text_search_flags", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function search_flags(
        Request $request
    ): JsonResponse
    {
        $search_service = $this->getContainer()->get(static::searchFlagServiceName);
        $data = $search_service->search($request);

        return new JsonResponse($data);
    }

    /**
     * @Route("/text/paginate", name="text_paginate", methods={"GET"})
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
     * @Route("/text/export/csv", name="text_export_csv", methods={"GET"})
     * @param Request $request
     * @return StreamedCsvResponse
     */
    public function exportCSV(
        Request $request
    ): StreamedCsvResponse
    {
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
    public function getSingle(int $id, Request $request): JsonResponse|Response
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
            try {
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
            } catch(Exception $e) {
                throw $this->createNotFoundException('The text does not exist');
            }
        }
    }

    /**
     * @Route("/text/{id}/annotations", name="text_get_annotations", methods={"GET"})
     * @param int $id
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function getAnnotations(int $id, Request $request): JsonResponse|Response
    {
        $preloadRelations = [
            'typographyAnnotations',
            'typographyAnnotations.textSelection',
            'typographyAnnotations.override',
            'morphologyAnnotations',
            'morphologyAnnotations.textSelection',
            'morphologyAnnotations.override',
            'lexisAnnotations',
            'lexisAnnotations.textSelection',
            'lexisAnnotations.override',
            'orthographyAnnotations',
            'orthographyAnnotations.textSelection',
            'orthographyAnnotations.override',
            'morphoSyntacticalAnnotations',
            'morphoSyntacticalAnnotations.textSelection',
            'morphoSyntacticalAnnotations.override',
            'handshiftAnnotations',
            'handshiftAnnotations.textSelection',
            'handshiftAnnotations.override',
            'languageAnnotations',
            'languageAnnotations.textSelection',
            'languageAnnotations.override',
            'genericTextStructures',
            'genericTextStructures.textSelection',
            'genericTextStructures.override',
            'layoutTextStructures',
            'layoutTextStructures.textSelection',
            'layoutTextStructures.override',
            'genericTextStructureAnnotations',
            'genericTextStructureAnnotations.textSelection',
            'genericTextStructureAnnotations.override',
            'layoutTextStructureAnnotations',
            'layoutTextStructureAnnotations.textSelection',
            'layoutTextStructureAnnotations.override',
            'flags',
        ];

        /** @var TextRepository $repo */
        $repo = $this->getContainer()->get('text_repository');

        try {
            $text = $repo->find($id, $preloadRelations);
            if (!$text) {
                throw new Exception('Text not found');
            }

            $res = new ElasticTextAnnotationsResource($text);
            return new JsonResponse($res->toArray());
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => ['code' => Response::HTTP_NOT_FOUND, 'message' => $e->getMessage()]],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * @Route("/text/{id}/flags", name="text_flags_update", methods={"PATCH"})
     * @param int $id
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function patchTextFlags(string $id, Request $request): JsonResponse
    {

        // get morphMap
        $morphMap = Relation::morphMap();

        $flags = json_decode($request->getContent(), true);


        if (!isset($flags['needs_attention'], $flags['review_done'])) {
            return $this->jsonFail('Missing required properties', $flags);
        }


        // insert/update flags

        try {
            $repo = $this->getContainer()->get('text_repository');
            $record = $repo->find($id, ['flags']);


            if (!$record) {
                return $this->jsonFail("Text not found", $id);
            }

            $record->flags()->updateOrCreate(['text_id' => $id], $flags);

            return $this->jsonSuccess('Annotation override successful', $flags);
        } catch (Throwable $e) {
            return $this->jsonError($e->getMessage(), $id);
        }
    }

    /**
     * @Route("/annotation/{annotationType}/{annotationId}/override", name="annotation_override", methods={"PATCH"})
     */
    public function overrideAnnotation(string $annotationType, int $annotationId, Request $request): JsonResponse
    {
        // get morphMap
        $morphMap = Relation::morphMap();

        // validate request
        $annotation = json_decode($request->getContent(), true);
        if (!is_array($annotation)) {
            return $this->jsonFail('Invalid input data', $annotation);
        }
        if (!isset($annotation['selection_start'], $annotation['selection_end'], $annotation['is_deleted'])) {
            return $this->jsonFail('Missing required properties', $annotation);
        }
        if (!isset($morphMap[$annotationType])) {
            return $this->jsonFail("Invalid annotation type ({$annotationType})", $annotation);
        }

        // insert/update annotation overrides
        try {
            /** @var class-string<AbstractAnnotationModel> $modelClass */
            $modelClass = $morphMap[$annotationType];

            $record = $model::find($annotationId);
            if (!$record) {
                return $this->jsonFail("Annotation not found", $annotation);
            }

            $record->override()->updateOrCreate(['annotation_id' => $annotationId, 'annotation_type' => $annotationType], $annotation);

            return $this->jsonSuccess('Annotation override successful', $annotation);
        } catch (Throwable $e) {
            return $this->jsonError($e->getMessage(), $annotation);
        }
    }

}
