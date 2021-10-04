<?php

namespace App\Controller;

use App\Service\ElasticSearch\BaseAnnotationSearchService;

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
     * @Route("/annotation/search", name="annotation_search", methods={"GET"})
     * @param Request $request
     * @param BaseAnnotationSearchService $elasticService
     * @return Response
     */
    public function search(
        Request $request,
        BaseAnnotationSearchService $elasticService
    ) {
        return $this->render(
            $this->templateFolder. '/overview.html.twig',
            [
                'urls' => json_encode([
                    // @codingStandardsIgnoreStart Generic.Files.LineLength
                    'search_api' => $this->generateUrl('annotation_search_api'),
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
     * @Route("/annotation/search_api", name="annotation_search_api", methods={"GET"})
     * @param Request $request
     * @param BaseAnnotationSearchService $elasticService
     * @return JsonResponse
     */
    public function searchAPI(
        Request $request,
        BaseAnnotationSearchService $elasticService
    ) {
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
