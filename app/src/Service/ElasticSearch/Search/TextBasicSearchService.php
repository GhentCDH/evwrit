<?php

namespace App\Service\ElasticSearch\Search;

use App\Service\ElasticSearch\Base\AbstractSearchService;
use App\Service\ElasticSearch\Client;
use Elastica\Settings;

class TextBasicSearchService extends AbstractSearchService
{
    protected const indexName = "texts";

    const ignoreUnknownUncertain = ['unknown','uncertain', 'Unknown', 'Uncertain', 'Unknwon'];

    public function __construct(Client $client, string $indexPrefix, Configs $config, bool $debug = false)
    {
        parent::__construct($client, $indexPrefix, $debug);
        $this->config = $config;
    }

    protected function initSearchConfig(): array {
        $searchFilters = array_merge(
            $this->config->filterPhysicalInfo(),
            $this->config->filterCommunicativeInfo(),
            $this->config->filterAncientPerson(),
            $this->config->filterAdministrative(),
            $this->config->filterCharacterRecognitionTool(),
            array_filter($this->config->filterMateriality(), fn($key) => $key === 'material', ARRAY_FILTER_USE_KEY)
        );

        return $searchFilters;
    }

    protected function initAggregationConfig(): array {
        $aggregationFilters = array_merge(
            $this->config->aggregatePhysicalInfo(),
            $this->config->aggregateCommunicativeInfo(),
            $this->config->aggregateAncientPerson(),
            $this->config->aggregateAdministrative(),
            array_filter($this->config->aggregateMateriality(), fn($key) => $key === 'material', ARRAY_FILTER_USE_KEY)
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