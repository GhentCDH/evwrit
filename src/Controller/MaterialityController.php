<?php

namespace App\Controller;

use App\Service\ElasticSearchService\TextMaterialityElasticService;
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
     * @param TextMaterialityElasticService $elasticservice
     * @return Response
     */
    public function search(
        Request $request,
        TextMaterialityElasticService $elasticService
    ) {
        return $this->render(
            $this->templateFolder. '/overview.html.twig',
            [
                'urls' => json_encode([
                    // @codingStandardsIgnoreStart Generic.Files.LineLength
                    'search_api' => $this->generateUrl('materiality_search_api'),
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
     * @Route("/materiality/search_api", name="materiality_search_api", methods={"GET"})
     * @param Request $request
     * @param TextMaterialityElasticService $elasticService
     * @return JsonResponse
     */
    public function searchAPI(
        Request $request,
        TextMaterialityElasticService $elasticService
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
