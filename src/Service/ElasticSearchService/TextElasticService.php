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

    protected function getMappingProperties(): array {
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
            'archive' => ['type' => 'nested'],
            'era' => ['type' => 'nested'],
            'keyword' => ['type' => 'nested'],
            'language' => ['type' => 'nested'],
            'material' => ['type' => 'nested'],
            'project' => ['type' => 'nested'],
            'social_distance' => ['type' => 'nested'],
            'text_type' => ['type' => 'nested'],
            'text_subtype' => ['type' => 'nested'],
            'location_found' => ['type' => 'nested'],
            'location_written' => ['type' => 'nested'],
            'agentive_role' => [
                'type' => 'nested',
            ],
            'communicative_goal'  => [
                'type' => 'nested',
            ],
        ];
    }

    protected function getIndexProperties(): array {
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
            'archive' => ['type' => self::FILTER_NESTED],
            'agentive_role' => [
                'type' => self::FILTER_NESTED,
                'path' => 'agentive_role'
            ],
            'communicative_goal' => [
                'type' => self::FILTER_NESTED,
                'path' => 'communicative_goal'
            ],
            'era' => ['type' => self::FILTER_NESTED],
            'generic_agentive_role' => [
                'type' => self::FILTER_NESTED,
                'path' => 'agentive_role'
            ],
            'generic_communicative_goal' => [
                'type' => self::FILTER_NESTED,
                'path' => 'communicative_goal'
            ],
            'keyword' => ['type' => self::FILTER_NESTED],
            'language' => ['type' => self::FILTER_NESTED],
            'location_written' => ['type' => self::FILTER_NESTED],
            'location_found' => ['type' => self::FILTER_NESTED],
            'material' => ['type' => self::FILTER_NESTED],
            'project' => ['type' => self::FILTER_NESTED],
            'social_distance' => ['type' => self::FILTER_NESTED],
            'text_type' => ['type' => self::FILTER_NESTED],
            'text_subtype' => ['type' => self::FILTER_NESTED],
        ];

        // add extra filters if user role allows
        // ...

        return $searchFilters;
    }

    protected function getAggregationFilterConfig(): array {
        $aggregationFilters = [
            'agentive_role' => [
                'type' => self::AGG_NESTED,
                'path' => 'agentive_role',
                'filter' => [ 'generic_agentive_role.id' => 'generic_agentive_role' ]
            ],
            'communicative_goal' => [
                'type' => self::AGG_NESTED,
                'path' => 'communicative_goal',
                'filter' => [ 'generic_communicative_goal.id' => 'generic_communicative_goal' ]
            ],
            'archive' => ['type' => self::AGG_NESTED],
            'era' => ['type' => self::AGG_NESTED],
            'generic_agentive_role' => [
                'type' => self::FILTER_NESTED,
                'path' => 'agentive_role',
            ],
            'generic_communicative_goal' => [
                'type' => self::FILTER_NESTED,
                'path' => 'communicative_goal',
            ],
            'form'  => ['type' => self::AGG_NESTED],
            'keyword' => ['type' => self::AGG_NESTED],
            'location_written' => ['type' => self::AGG_NESTED],
            'location_found' => ['type' => self::AGG_NESTED],
            'material'  => ['type' => self::AGG_NESTED],
            'script' => ['type' => self::AGG_NESTED],
            'social_distance' => ['type' => self::AGG_NESTED],
            'text_type' => ['type' => self::AGG_NESTED],
            'text_subtype' => ['type' => self::AGG_NESTED],
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
        $returnProps = ['id', 'tm_id', 'title'];

        $result = array_intersect_key($result, array_flip($returnProps));

        return $result;
    }

}
