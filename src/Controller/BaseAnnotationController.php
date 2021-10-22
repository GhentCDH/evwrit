<?php

namespace App\Controller;

use App\Service\ElasticSearch\AnnotationSearchService;

use App\Service\ElasticSearch\LanguageTypographyAnnotationSearchService;
use App\Service\ElasticSearch\LinguisticAnnotationSearchService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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


        return $this->render(
            $this->templateFolder. '/overview.html.twig',
            [
                'title' => 'Linguistic annotations',
                'urls' => json_encode([
                    // @codingStandardsIgnoreStart Generic.Files.LineLength
                    'search_api' => $this->generateUrl('annotation_linguistic_search_api'),
                    'get_single' => $this->generateUrl('text_get_single', ['id' => 'text_id']),
                    // @codingStandardsIgnoreEnd
                ]),
                'data' => json_encode(
                    $elasticService->searchAndAggregate(
                        $this->sanitize($request->query->all())
                    )
                ),
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
            $this->sanitize($request->query->all())
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
                    // @codingStandardsIgnoreEnd
                ]),
                'data' => json_encode(
                    $elasticService->searchAndAggregate(
                        $this->sanitize($request->query->all())
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
            $this->sanitize($request->query->all())
        );

        return new JsonResponse($result);
    }

    /**
     * Sanitize data from request string
     * @param array $params
     * @return array
     */
    private function sanitize(array $params): array
    {
        return $params;
    }
}
