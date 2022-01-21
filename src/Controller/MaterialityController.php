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
     * @param TextMaterialitySearchService $elasticservice
     * @return Response
     */
    public function search(
        Request $request,
        TextMaterialitySearchService $elasticService
    ) {
        return $this->render(
            $this->templateFolder. '/overview.html.twig',
            [
                'urls' => json_encode([
                    // @codingStandardsIgnoreStart Generic.Files.LineLength
                    'search_api' => $this->generateUrl('materiality_search_api'),
                    'text_get_single' => $this->generateUrl('text_get_single', ['id' => 'text_id']),
                    // @codingStandardsIgnoreEnd
                ]),
                'data' => json_encode(
                    $elasticService->searchAndAggregate(
                        $this->sanitize($request->query->all())
                    )
                ),
                'identifiers' => json_encode([]),
                'managements' => json_encode([]),
                'title' => 'Materiality'
            ]
        );
    }



    /**
     * @Route("/materiality/search_api", name="materiality_search_api", methods={"GET"})
     * @param Request $request
     * @param TextMaterialitySearchService $elasticService
     * @return JsonResponse
     */
    public function searchAPI(
        Request $request,
        TextMaterialitySearchService $elasticService
    ) {
        $result = $elasticService->searchAndAggregate(
            $this->sanitize($request->query->all())
        );

        return new JsonResponse($result);
    }

    /**
     * @Route("/materiality/export/csv", name="text_export_csv", methods={"GET"})
     * @param Request $request
     * @param TextMaterialitySearchService $elasticService
     * @return StreamedCsvResponse
     */
    public function exportCSV(
        Request $request,
        TextMaterialitySearchService $elasticService
    ) {
        // search
        $data = $elasticService->searchRAW(
            $this->sanitize($request->query->all())
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
