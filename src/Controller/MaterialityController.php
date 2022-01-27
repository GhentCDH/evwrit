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

    protected const searchServiceName = "text_materiality_search_service";
    protected const indexServiceName = "text_index_service";

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
     * @return Response
     */
    public function search(
        Request $request
    ) {
        return $this->_search(
            $request,
            [
                'title' => 'Materiality'
            ],
            [
                'search_api' => 'materiality_search_api',
                'paginate' => 'materiality_paginate',
                'export_csv' => 'materiality_export_csv'
            ]
        );
    }

    /**
     * @Route("/materiality/search_api", name="materiality_search_api", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function search_api(
        Request $request
    ) {
        return $this->_search_api($request);
    }

    /**
     * @Route("/materiality/paginate", name="materiality_paginate", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function paginate(
        Request $request
    ) {
        return $this->_paginate($request);
    }

    /**
     * @Route("/materiality/export/csv", name="materiality_export_csv", methods={"GET"})
     * @param Request $request
     * @param TextMaterialitySearchService $elasticService
     * @return StreamedCsvResponse
     */
    public function exportCSV(
        Request $request,
        TextMaterialitySearchService $elasticService
    ) {
        $elasticService = $this->getContainer()->get(static::searchServiceName);

        // search
        $data = $elasticService->searchRAW(
            $this->sanitizeSearchRequest($request->query->all())
        );

        // header
        $csvHeader = ['id', 'tm_id', 'year_begin', 'year_end', 'text'];

        $dataMap = [
            'id', 'tm_id', 'year_start', 'year_end', 'text',
            'language', 'text_type', 'text_subtype',
            'width', 'height',
            'margin_bottom', 'margin_top', 'margin_left','margin_right',
            'lines', 'letters_per_line', 'interlinear_space',
            'is_recto', 'is_verso', 'is_transversa_charta'
        ];

        $dataMap = array_combine($dataMap, $dataMap);

        $dataFormatter = [
            'bool' => [ 'is_recto', 'is_verso', 'is_transversa_charta' ],
            'min_max' => ['lines', 'letters_per_line', 'interlinear_space'],
            'id_name' => ['text_type', 'text_subtype', 'text_format', 'material', 'language']
        ];

        // convert formatter type|keys to key|type
        foreach($dataFormatter as $type => $keys) {
            foreach($keys as $key) {
                $dataFormatter[$key] = $type;
            }
            unset($dataFormatter[$type]);
        }


        // convert data to csv array using dataMapper and dataFormatter
        $csvData = [];
        $csvHeader = array_keys($dataMap);
        foreach ($data['data'] as $row) {
            $csvRow = [];

            foreach($dataMap as $csvKey => $jsonKey) {
                $value = $this->getValueByJsonKey($jsonKey, $row);
                $ret = null;
                switch($dataFormatter[$csvKey] ?? 'default')
                {
                    case 'bool':
                        $ret = $value ? 'yes' : 'no';
                        break;
                    case 'id_name':
                        if ( !is_array($value) ) {
                            break;
                        }
                        $values = $this->is_associative($value) ? [$value] : $value;
                        $values = array_reduce($values, function($carry, $item) {
                            if (isset($item['name'])) $carry[] = $item['name']; return $carry;
                        }, []);
                        $ret = count($values) ? implode(',', $values) : null;
                        break;
                    default:
                        $ret = print_r($value, true);
                        break;
                }
                $csvRow[$csvKey] = $ret;
            }

            $csvData[] = $csvRow;
        }

        // csv response
        $response = new StreamedCsvResponse($csvData, $csvHeader, 'materiality.csv');
        return $response;
    }

    protected function getValueByJsonKey($key, array $data, $default = null)
    {
        // @assert $key is a non-empty string
        // @assert $data is a loopable array
        // @otherwise return $default value
        if (!is_string($key) || empty($key) || !count($data)) {
            return $default;
        }

        // @assert $key contains a dot notated string
        if (strpos($key, '.') !== false)
        {
            $keys = explode('.', $key);

            foreach ($keys as $innerKey) {
                // @assert $data[$innerKey] is available to continue
                // @otherwise return $default value
                if (!array_key_exists($innerKey, $data)) {
                    return $default;
                }

                $data = $data[$innerKey];
            }

            return $data;
        }

        // @fallback returning value of $key in $data or $default value
        return $data[$key] ?? $default;
    }

    protected function is_associative(array $inpt_arr)
    {
        if ([] === $inpt_arr) {
            return true;
        }

        if(array_keys($inpt_arr) !== range(0, count($inpt_arr) - 1)) {
            return true;
        }
        // Dealing with a Sequential array
        return false;
    }

}
