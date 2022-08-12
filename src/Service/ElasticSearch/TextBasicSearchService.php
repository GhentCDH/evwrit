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
            Configs::aggregateAncientPerson(),
            Configs::aggregateAdministrative(),
            array_filter(Configs::aggregateMateriality(), fn($key) => $key === 'material', ARRAY_FILTER_USE_KEY)
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
