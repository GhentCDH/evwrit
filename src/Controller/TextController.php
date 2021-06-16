<?php

namespace App\Controller;

use App\Resource\TextElasticResource;
use App\Resource\TextResource;
use App\Service\ElasticSearchService\ElasticSearchService;
use App\Service\ElasticSearchService\TextElasticService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\TextRepository;

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
     * @param TextElasticService $elasticservice
     * @return Response
     */
    public function search(
        Request $request,
        TextElasticService $elasticService
    ) {
        return $this->render(
            $this->templateFolder. '/overview.html.twig',
            [
                'urls' => json_encode([
                    // @codingStandardsIgnoreStart Generic.Files.LineLength
                    'text_search_api' => $this->generateUrl('text_search_api'),
                    'text_get_single' => $this->generateUrl('text_get_single', ['id' => 'text_id']),
                    // @codingStandardsIgnoreEnd
                ]),
                'data' => json_encode(
                    $elasticService->searchAndAggregate(
                        $this->sanitize($request->query->all()), ElasticSearchService::ENABLE_CACHE
                    )
                ),
                'identifiers' => json_encode([]),
                'managements' => json_encode([])
            ]
        );
    }

    /**
     * @Route("/text/search_api", name="text_search_api", methods={"GET"})
     * @param Request $request
     * @param TextElasticService $elasticService
     * @return JsonResponse
     */
    public function searchAPI(
        Request $request,
        TextElasticService $elasticService
    ) {
        $result = $elasticService->searchAndAggregate(
            $this->sanitize($request->query->all()), ElasticSearchService::ENABLE_CACHE
        );

        return new JsonResponse($result);
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
//        if (explode(',', $request->headers->get('Accept'))[0] == 'application/json') {
        if (in_array('application/json', $request->getAcceptableContentTypes())) {


            //$this->denyAccessUnlessGranted('ROLE_EDITOR_VIEW');
            try {
                $repository = $container->get('text_repository' );
                $model = $repository->find($id);
                $resource = new TextResource($model);
            } catch (NotFoundHttpException $e) {
                return new JsonResponse(
                    ['error' => ['code' => Response::HTTP_NOT_FOUND, 'message' => $e->getMessage()]],
                    Response::HTTP_NOT_FOUND
                );
            }
            return new JsonResponse($resource->toJson());
        } else {
            // Let the 404 page handle the not found exception
            $repository = $container->get('text_repository' );
            $model = $repository->find($id);
            $resource = new TextResource($model);

            dump($model->text);
            dump(str_replace("\v","\n",$model->text));

            dump($resource->toJson());
            return $this->render(
                $this->templateFolder. '/detail.html.twig',
                [
                    'urls' => json_encode([
                        // @codingStandardsIgnoreStart Generic.Files.LineLength
                        'text_search_api' => $this->generateUrl('text_search_api'),
                        'text_get_single' => $this->generateUrl('text_get_single', ['id' => 'text_id']),
                        // @codingStandardsIgnoreEnd
                    ]),
                    'data' => json_encode([
                        $resource::CACHENAME => $resource
                    ])
                ]
            );
        }
    }

    /**
     * @Route("/text/dump", name="text_dump", methods={"GET"})
     */
    public function dump(Request $request): Response
    {
        $repository = $this->container->get('text_repository' );
        $text = $repository->find(69108);

        $res = new TextElasticResource($text);
        $arr = $res->toJson(0);

        dump($arr);
        //print_r($text->era->name);

        /*
        $texts = TextRepository::queryAll()->where('text_id', '<', 100)->with(['scripts','forms'])->chunk(20, function($res) {
            foreach ($res as $text) {
                $res = new TextResource($text);
                print_r($res->toJson());
            }
        });
        */

        //$text = TextResource::collection();

//        $repository = $container->get('text_repository' );
//        $texts = $repository->findAll(5);
//
//        $col = TextResource::collection($texts);
//        dump($col);
//        $col->toJson();

//        foreach( $texts as $text ) {
//            $ret = $text->toArray(null);
//            print_r($ret);
//        }


        return $this->render('Text/index.html.twig', [
            'controller_name' => 'TextController',
        ]);
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
