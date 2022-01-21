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
     * @param TextBasicSearchService $elasticservice
     * @return Response
     */
    public function search(
        Request $request,
        TextBasicSearchService $elasticService
    ) {
        $data = $elasticService->searchAndAggregate(
            $this->sanitize($request->query->all())
        );

//        dump($data['data'][0]);

        return $this->render(
            $this->templateFolder. '/overview.html.twig',
            [
                'urls' => json_encode([
                    // @codingStandardsIgnoreStart Generic.Files.LineLength
                    'text_search_api' => $this->generateUrl('text_search_api'),
                    'text_get_single' => $this->generateUrl('text_get_single', ['id' => 'text_id']),
                    'export_csv' => $this->generateUrl('text_export_csv'),
                    // @codingStandardsIgnoreEnd
                ]),
                'data' => json_encode($data),
                'identifiers' => json_encode([]),
                'managements' => json_encode([]),
                'title' => 'Texts'
            ]
        );
    }

    /**
     * @Route("/text/search_api", name="text_search_api", methods={"GET"})
     * @param Request $request
     * @param TextBasicSearchService $elasticService
     * @return JsonResponse
     */
    public function searchAPI(
        Request $request,
        TextBasicSearchService $elasticService
    ) {
        $data = $elasticService->searchAndAggregate(
            $this->sanitize($request->query->all())
        );

        return new JsonResponse($data);
    }

    /**
     * @Route("/text/export/csv", name="text_export_csv", methods={"GET"})
     * @param Request $request
     * @param TextBasicSearchService $elasticService
     * @return StreamedCsvResponse
     */
    public function exportCSV(
        Request $request,
        TextBasicSearchService $elasticService
    ) {
        $header = [];

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
        $response = new StreamedCsvResponse($csvData, $csvData[0], 'texts.csv');
        return $response;
    }


    /**
     * @Route("/text/{id}", name="text_get_single", methods={"GET"})
     * @param int $id
     * @param Request $request
     * @param ContainerInterface $container
     * @return JsonResponse|Response
     */
    public function getSingle(int $id, Request $request, ContainerInterface $container)
    {
        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            //$this->denyAccessUnlessGranted('ROLE_EDITOR_VIEW');
            try {
                $service = $container->get('text_index_service');
                $resource = $service->get($id);
            } catch (NotFoundHttpException $e) {
                return new JsonResponse(
                    ['error' => ['code' => Response::HTTP_NOT_FOUND, 'message' => $e->getMessage()]],
                    Response::HTTP_NOT_FOUND
                );
            }
            return new JsonResponse($resource);
        } else {
            // Let the 404 page handle the not found exception
            $service = $container->get('text_index_service');
            $resource = $service->get($id);

            return $this->render(
                $this->templateFolder. '/detail.html.twig',
                [
                    'urls' => json_encode([
                        // @codingStandardsIgnoreStart Generic.Files.LineLength
                        'text_search' => $this->generateUrl('text_search'),
                        'materiality_search' => $this->generateUrl('materiality_search'),
                        'text_search_api' => $this->generateUrl('text_search_api'),
                        'text_get_single' => $this->generateUrl('text_get_single', ['id' => 'text_id']),
                        // @codingStandardsIgnoreEnd
                    ]),
                    'data' => json_encode([
                        'text' => $resource
                    ])
                ]
            );
        }
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
