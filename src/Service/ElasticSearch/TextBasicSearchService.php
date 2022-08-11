<?php

namespace App\Service\ElasticSearch;

use Elastica\Mapping;
use Elastica\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TextBasicSearchService extends AbstractSearchService
{
    const indexName = "texts";

    const ignoreUnknownUncertain = ['unknown','uncertain', 'Unknown', 'Uncertain', 'Unknwon'];

    public function __construct(Client $client)
    {
        parent::__construct(
            $client,
            self::indexName);
    }

    protected function getSearchFilterConfig(): array {
        $searchFilters = array_merge(
            Configs::filterPhysicalInfo(),
            Configs::filterCommunicativeInfo(),
            Configs::filterAncientPerson(),
            Configs::filterAdministrative(),
            array_filter(Configs::filterMateriality(), fn($key) => $key === 'material', ARRAY_FILTER_USE_KEY)
        );

        return $searchFilters;
    }

    protected function getAggregationConfig(): array {
        $aggregationFilters = array_merge(
            Configs::aggregatePhysicalInfo(),
            Configs::aggregateCommunicativeInfo(),
            Configs::aggregateAdministrative(),
            array_filter(Configs::aggregateMateriality(), fn($key) => $key === 'material', ARRAY_FILTER_USE_KEY)
        );

        $ap_filters = [
            'ap_name' => ['field' => 'name','type' => self::FILTER_KEYWORD],
            'ap_tm_id' => ['field' => 'tm_id', 'type' => self::FILTER_NUMERIC],
            'ap_role' => ['field' => 'role','type' => self::FILTER_OBJECT_ID],
            'ap_gender' => ['field' => 'gender','type' => self::FILTER_OBJECT_ID],
            'ap_occupation' => ['field' => 'occupation','type' => self::FILTER_OBJECT_ID],
            'ap_social_rank' => ['field' => 'social_rank','type' => self::FILTER_OBJECT_ID],
            'ap_honorific_epithet' => ['field' => 'honorific_epithet','type' => self::FILTER_OBJECT_ID],
            'ap_graph_type' => ['field' => 'graph_type','type' => self::FILTER_OBJECT_ID],
        ];

        $aggregationFilters['ap_name'] = [
            'type' => self::AGG_KEYWORD, 
            'field' => 'name',
            'nested_path' => 'ancient_person',
            'filter' => array_diff_key($ap_filters, array_flip(['ap_name'])),
        ];
        $aggregationFilters['ap_tm_id'] = [
            'type' => self::AGG_NUMERIC, 
            'field' => 'tm_id',
            'nested_path' => 'ancient_person',
            'filter' => array_diff_key($ap_filters, array_flip(['tm_id'])),
        ];
        $aggregationFilters['ap_role'] = [
            'type' => self::AGG_NESTED_ID_NAME, 
            'field' => 'role',
            'nested_path' => 'ancient_person',
            'ignoreValue' => self::ignoreUnknownUncertain,
            'filter' => array_diff_key($ap_filters, array_flip(['ap_role'])),
        ];
        $aggregationFilters['ap_gender'] = [
            'type' => self::AGG_NESTED_ID_NAME, 
            'field' => 'gender',
            'nested_path' => 'ancient_person',
            'ignoreValue' => self::ignoreUnknownUncertain,
            'filter' => array_diff_key($ap_filters, array_flip(['ap_gender'])),
        ];
        $aggregationFilters['ap_occupation'] = [
            'type' => self::AGG_NESTED_ID_NAME, 
            'field' => 'occupation',
            'nested_path' => 'ancient_person',
            'ignoreValue' => self::ignoreUnknownUncertain,
            'filter' => array_diff_key($ap_filters, array_flip(['ap_occupation'])),
        ];
        $aggregationFilters['ap_social_rank'] = [
            'type' => self::AGG_NESTED_ID_NAME, 
            'field' => 'social_rank',
            'nested_path' => 'ancient_person',
            'ignoreValue' => self::ignoreUnknownUncertain,
            'filter' => array_diff_key($ap_filters, array_flip(['ap_social_rank'])),
        ];
        $aggregationFilters['ap_honorific_epithet'] = [
            'type' => self::AGG_NESTED_ID_NAME, 
            'field' => 'honorific_epithet',
            'nested_path' => 'ancient_person',
            'ignoreValue' => self::ignoreUnknownUncertain,
            'filter' => array_diff_key($ap_filters, array_flip(['ap_honorific_epithet'])),
        ];
        $aggregationFilters['ap_graph_type'] = [
            'type' => self::AGG_NESTED_ID_NAME, 
            'field' => 'graph_type',
            'nested_path' => 'ancient_person',
            'ignoreValue' => self::ignoreUnknownUncertain,
            'filter' => array_diff_key($ap_filters, array_flip(['ap_graph_type'])),
        ];

        // add extra filters if user role allows
        // ...

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
        $returnProps = ['id', 'tm_id', 'title', 'year_begin', 'year_end', 'text_type', 'location_found'];

        $result = array_intersect_key($result, array_flip($returnProps));

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
                case 'year_begin':
                case 'year_end':
                case 'id':
                case 'tm_id':
                    $params['orderBy'] = [ $params['orderBy'] ];
                    break;
                default:
                    unset($params['orderBy']);
                    break;
            }
        }

        return parent::sanitizeSearchParameters($params, $merge_defaults);
    }

}
