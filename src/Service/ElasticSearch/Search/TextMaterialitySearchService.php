<?php

namespace App\Service\ElasticSearch\Search;

use App\Service\ElasticSearch\Client;

class TextMaterialitySearchService extends AbstractSearchService
{
    const indexName = "texts";

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
            Configs::filterMateriality(),
            Configs::filterAncientPerson(),
            Configs::filterAdministrative(),
        );

        // add extra filters if user role allows
        // ...

        return $searchFilters;
    }

    protected function getAggregationConfig(): array {
        $aggregationFilters = array_merge(
            Configs::aggregatePhysicalInfo(),
            Configs::aggregateCommunicativeInfo(),
            Configs::aggregateMateriality(),
            Configs::aggregateAncientPerson(),
            Configs::aggregateAdministrative(),
        );

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
        $returnProps = ['id', 'tm_id', 'title', 'year_begin', 'year_end', 'level_category', 'location_found'];

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
