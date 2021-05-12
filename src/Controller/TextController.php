<?php

namespace App\Controller;

use App\Model\Text;
use App\Resource\TextResource;
use App\Service\ElasticSearchService\TextElasticService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\TextRepository;

class TextController extends AbstractController
{
    /**
     * @Route("/text", name="text")
     */
    public function index(Request $request, ContainerInterface $container): Response
    {

        //        $text = TextRepository::find(69108);
//        $res = new TextResource($text);
//        print_r($res->toJson());

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
//        $texts = TextRepository::findAll(5);
//        foreach( $texts as $text ) {
//            $ret = $text->toArray(null);
//            print_r($ret);
//        }


        return $this->render('text/index.html.twig', [
            'controller_name' => 'TextController',
        ]);
    }

    /**
     * @Route("/text/search", name="text_search")
     * @Method("GET")
     * @param Request $request
     * @param TextElasticService $elasticservice
     * @return Response
     */
    public function search(
        Request $request,
        TextElasticService $elasticService
    ) {
        return $this->render(
            'Text/overview.html.twig',
            [
                'urls' => json_encode([
                    // @codingStandardsIgnoreStart Generic.Files.LineLength
                    'text_search_api' => $this->generateUrl('text_search_api'),
//                    'login' => $this->generateUrl('saml_login'),
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
     * @Route("/text/search_api", name="text_search_api")
     * @Method("GET")
     * @param Request $request
     * @param TextElasticService $elasticService
     * @return JsonResponse
     */
    public function searchAPI(
        Request $request,
        TextElasticService $elasticService
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
