<?php

namespace App\Service\ElasticSearchService;

use Elastica\Mapping;
use Elastica\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TextElasticService extends ElasticBaseService
{
    const indexName = "texts";

    public function __construct(ElasticSearchClient $client, ContainerInterface $container)
    {
        parent::__construct(
            $client,
            self::indexName);
    }

    public function setup(): void
    {
        $index = $this->getIndex();

        // delete index
        if ($index->exists()) {
            $index->delete();
        }

        // configure analysis
        $index->create($this->getIndexProperties());

        // configure mapping
        $mapProperties = $this->getMappingProperties();
        if (count($mapProperties)) {
            $mapping = new Mapping;
            $mapping->setProperties($mapProperties);
            $mapping->send($this->getIndex());
        }
    }

    protected function getMappingProperties() {
        return [
            'title' => [
                'type' => 'text',
                // Needed for sorting
                'fields' => [
                    'keyword' => [
                        'type' => 'keyword',
                        'normalizer' => 'case_insensitive',
                        'ignore_above' => 256,
                    ],
                ],
            ],
            'era' => ['type' => 'nested'],
            'archive' => ['type' => 'nested'],
            'project' => ['type' => 'nested'],
            'script' => ['type' => 'nested'],
            'form' => ['type' => 'nested'],
            'material' => ['type' => 'nested'],
            'social_distance' => ['type' => 'nested'],
        ];
    }

    protected function getIndexProperties() {
        return [
            'settings' => [
                'analysis' => Analysis::ANALYSIS
            ]
        ];
    }

    protected function getSearchFilterConfig(): array {
        $searchFilters = [
            'title' => ['type' => self::FILTER_TEXT],
            'id' => ['type' => self::FILTER_NUMERIC],
            'tm_id' => ['type' => self::FILTER_NUMERIC],
            'script' => ['type' => self::FILTER_NESTED],
            'form' => ['type' => self::FILTER_NESTED],
            'material' => ['type' => self::FILTER_NESTED],
            'social_distance' => ['type' => self::FILTER_NESTED],
            'era' => ['type' => self::FILTER_NESTED],
            'archive' => ['type' => self::FILTER_NESTED],
            'project' => ['type' => self::FILTER_NESTED],
        ];

        // add extra filters if user role allows
        // ...

        return $searchFilters;
    }

    protected function getAggregationFilterConfig(): array {
        $aggregationFilters = [
            'script' => ['type' => self::AGG_NESTED],
            'form'  => ['type' => self::AGG_NESTED],
            'material'  => ['type' => self::AGG_NESTED],
            'social_distance' => ['type' => self::AGG_NESTED],
            'era' => ['type' => self::AGG_NESTED],
            'archive' => ['type' => self::AGG_NESTED],
            'project' => ['type' => self::AGG_NESTED],
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

}
