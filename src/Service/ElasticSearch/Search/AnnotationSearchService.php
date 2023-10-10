<?php

namespace App\Service\ElasticSearch\Search;

use App\Service\ElasticSearch\Client;
use App\Service\ElasticSearch\Index\TextIndexService;
use Elastica\Query;
use Elastica\Settings;
use Elastica\Script;

class AnnotationSearchService extends AbstractSearchService
{
    protected const indexName = "texts";

    protected function getSearchFilterConfig(): array {
        $searchFilters = array_merge(
            Configs::filterPhysicalInfo(),
            Configs::filterCommunicativeInfo(),
            Configs::filterMateriality(),
            Configs::filterAncientPerson(),
            Configs::filterAdministrative(),
            Configs::filterBaseAnnotations(),
        );

        return $searchFilters;
    }

    protected function getAggregationConfig(): array {
        $aggregationFilters = array_merge(
            Configs::aggregatePhysicalInfo(),
            Configs::aggregateCommunicativeInfo(),
            Configs::aggregateMateriality(),
            Configs::aggregateAncientPerson(),
            Configs::aggregateAdministrative(),
            Configs::aggregateBaseAnnotations(),
        );

        return $aggregationFilters;
    }

    protected function getDefaultSearchParameters(): array {
        return [
            'limit' => 25,
            'page' => 1,
            'ascending' => 1,
            'orderBy' => ['title.keyword'],
        ];
    }

    protected function sanitizeSearchResult(array $result): array
    {
        $returnProps = [
            '_score',
            'id', 'tm_id', 'title', 'year_begin', 'year_end', 'line_count',
            'inner_hits', 'annotations',
            'level_category', 'location_found'
        ];

        $result = array_intersect_key($result, array_flip($returnProps));
        $result['annotations'] = $result['annotations'] ?? [];
        if ( isset($result['inner_hits']['annotations']) ) {
            $result['annotations'] = $result['inner_hits']['annotations']['data'];
            $result['annotations_hits_count'] = $result['inner_hits']['annotations']['count'];
        }
        unset($result['inner_hits']);
        return $result;
    }

    protected function sanitizeSearchParameters(array $params, bool $merge_defaults = true): array
    {
        if (isset($params['orderBy'])) {
            switch ($params['orderBy']) {
                // convert fieldname to elastic expression
                case 'title':
                    $params['orderBy'] = ['title.keyword'];

                    break;
                case 'id':
                case 'tm_id':
                case 'year_begin':
                case 'year_end':
                case 'frequency_per_line':
                case 'instances_in_text':
                    $params['orderBy'] = [ $params['orderBy'] ];
                    break;
                default:
                    unset($params['orderBy']);
                    break;
            }
        }

        return parent::sanitizeSearchParameters($params, $merge_defaults);
    }

    protected function onBeforeSearch(array &$searchParams, Query $query, Query\FunctionScore $queryFS)
    {
//        dump($searchParams);
        foreach ($searchParams['orderBy'] ?? [] as $index => $field) {
            // Use different score for annotation frequencies
            if ($field === 'frequency_per_line') {
                $script = new Script\Script("(_score)/doc['line_count'].value");
                $queryFS->addScriptScoreFunction($script);
                $queryFS->setParam('boost_mode', 'replace');

                $searchParams['orderBy'][$index] = '_score';
            }
            if ($field === 'instances_in_text') {
                $searchParams['orderBy'][$index] = '_score';
            }
        }

    }

}