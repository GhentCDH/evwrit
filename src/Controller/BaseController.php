<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class BaseController extends AbstractController
{
    /**
     * The folder where relevant templates are located.
     *
     * @var string
     */
    protected $templateFolder;

    /**
     * @var ContainerInterface
     */
    protected $mycontainer;

    public function __construct(ContainerInterface $container) {
        $this->mycontainer = $container;
    }

    protected function getContainer() {
        return $this->mycontainer;
    }

    /**
     * Return shared urls
     * @return array
     */
    protected function getSharedAppUrls() {
        // urls
        $urls = [
            // searches
            'text_search' => $this->generateUrl('text_search'),
            'materiality_search' => $this->generateUrl('materiality_search'),
            'linguistic_annotation_search' => $this->generateUrl('linguistic_annotation_search'),
            'languagetypography_annotation_search' => $this->generateUrl('languagetypography_annotation_search'),
            'text_structure_search' => $this->generateUrl('text_structure_search'),

            'text_search_api' => $this->generateUrl('text_search_api'),
            'materiality_search_api' => $this->generateUrl('materiality_search_api'),
            'linguistic_annotation_search_api' => $this->generateUrl('linguistic_annotation_search_api'),
            'languagetypography_annotation_search_api' => $this->generateUrl('languagetypography_annotation_search_api'),
            'text_structure_search_api' => $this->generateUrl('text_structure_search_api'),

            // paginate
            'text_paginate' => $this->generateUrl('text_paginate'),
            'materiality_paginate' => $this->generateUrl('materiality_paginate'),
            'linguistic_annotation_paginate' => $this->generateUrl('linguistic_annotation_paginate'),
            'languagetypography_annotation_paginate' => $this->generateUrl('languagetypography_annotation_paginate'),
            'text_structure_paginate' => $this->generateUrl('text_structure_paginate'),

            // export csv
            'text_export_csv' => $this->generateUrl('text_export_csv'),
            'materiality_export_csv' => $this->generateUrl('materiality_export_csv'),
            'linguistic_annotation_export_csv' => $this->generateUrl('linguistic_annotation_export_csv'),
            'languagetypography_annotation_export_csv' => $this->generateUrl('languagetypography_annotation_export_csv'),

            // text get single
            'text_get_single' => $this->generateUrl('text_get_single', ['id' => 'text_id']),
        ];

        return $urls;
    }


    protected function _paginate(Request $request, $returnField = 'id') {
        $elasticService = $this->getContainer()->get(static::searchServiceName);

        // search
        $data = $elasticService->searchRAW(
            $request->query->all(),
            [$returnField]
        );

        // return array of id's
        $result = [];
        foreach($data['data'] as $item) {
            $result[] = $item[$returnField];
        }

        return new JsonResponse($result);
    }

    protected function _search_api(Request $request) {
        $elasticService = $this->getContainer()->get(static::searchServiceName);

        // get data
        $data = $elasticService->searchAndAggregate(
            $this->sanitizeSearchRequest($request->query->all())
        );

        return new JsonResponse($data);
    }

    protected function _search(Request $request, array $props = [], array $extraRoutes = []) {
        $elasticService = $this->getContainer()->get(static::searchServiceName);

        // get data
        $data = $elasticService->searchAndAggregate(
            $this->sanitizeSearchRequest($request->query->all())
        );

        // urls
        $urls = $this->getSharedAppUrls();
        foreach( $extraRoutes as $key => $val ) {
            $urls[$key] = $urls[$val] ?? $val;
        }

        // html response
        return $this->render(
            $this->templateFolder. '/overview.html.twig',
            [
                'urls' => json_encode($urls),
                'data' => json_encode($data),
                'identifiers' => json_encode([]),
                'managements' => json_encode([]),
            ] + $props
        );
    }

    /**
     * Sanitize data from request string
     * @param array $params
     * @return array
     */
    protected function sanitizeSearchRequest(array $params): array
    {
        return $params;
    }

}