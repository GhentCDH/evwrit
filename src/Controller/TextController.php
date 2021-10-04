<?php

namespace App\Controller;

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
                        $this->sanitize($request->query->all()), AbstractSearchService::ENABLE_CACHE
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
     * @param TextBasicSearchService $elasticService
     * @return JsonResponse
     */
    public function searchAPI(
        Request $request,
        TextBasicSearchService $elasticService
    ) {
        $result = $elasticService->searchAndAggregate(
            $this->sanitize($request->query->all()), AbstractSearchService::ENABLE_CACHE
        );

        return new JsonResponse($result);
    }

    /**
     * @Route("/text/dump", name="text_dump", methods={"GET"})
     */
    public function dump(Request $request, ContainerInterface $container): Response
    {
        $repository = $container->get('text_repository');
        /** @var Text $text */
        $text = $repository->find(12059);

        $text->load(['languageAnnotations']);

        $res = new ElasticTextResource($text);

        $arrRes = $res->toArray();

        dump($arrRes);

        foreach( $arrRes['annotations'] as $annoType => $annos ) {
            foreach( $annos as $anno ) {
//                dump($anno['text_selection']['text']);
//                dump($anno['context']['text']);
            }
        }



        /* query test */
        /*
        $repository = $container->get('text_repository' );
        $texts = $repository->indexQuery()->where('text_id', '<', 500)->get();
        foreach ($texts as $text) {
            $res = new TextResource($text);
            $res->toJson();
        }
        */

        /*
        $texts = TextRepository::queryAll()->where('text_id', '<', 100)->with(['scripts','forms'])->chunk(20, function($res) {
            foreach ($res as $text) {
                $res = new TextResource($text);
                print_r($res->toJson());
            }
        });
        */

//        $repository = $container->get('typography_annotation_repository');
//        $annotation = $repository->find(75649);
//
//        $res = new ElasticTypographyAnnotationResource($annotation);
//        $arr = $res->toJson(0);
//
//        dump($arr);

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
            return new JsonResponse($resource);
        } else {
            // Let the 404 page handle the not found exception
            $repository = $container->get('text_repository' );
            $model = $repository->find($id);
            $resource = new TextResource($model);

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
                        $resource::CACHENAME => $resource
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
