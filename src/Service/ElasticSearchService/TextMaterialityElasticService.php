<?php

namespace App\Service\ElasticSearchService;

use Elastica\Mapping;
use Elastica\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TextMaterialityElasticService extends ElasticBaseService
{
    const indexName = "texts_materiality";

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
            'year_begin' => ['type' => 'short'],
            'year_end' => ['type' => 'short'],

            'production_stage' => ['type' => 'nested'],
            'material' => ['type' => 'nested'],
            'text_format' => ['type' => 'nested'],
            'writing_direction' => ['type' => 'nested'],

            'columns_min' => ['type' => 'short'],
            'columns_max' => ['type' => 'short'],
            'letters_per_line_min' => ['type' => 'short'],
            'letters_per_line_max' => ['type' => 'short'],

            'lines_min' => ['type' => 'short'],
            'lines_max' => ['type' => 'short'],

            'margin_left' => ['type' => 'half_float'],
            'margin_right' => ['type' => 'half_float'],
            'margin_top' => ['type' => 'half_float'],
            'margin_bottom' => ['type' => 'half_float'],
            'interlinear_space' => ['type' => 'half_float'],
            'width' => ['type' => 'half_float'],
            'height' => ['type' => 'half_float'],

            'era' => ['type' => 'nested'],
            'keyword' => ['type' => 'nested'],
            'language' => ['type' => 'nested'],
            'social_distance' => ['type' => 'nested'],
            'text_type' => ['type' => 'nested'],
            'text_subtype' => ['type' => 'nested'],
            'agentive_role' => ['type' => 'nested'],
            'communicative_goal'  => ['type' => 'nested'],
            'project' => ['type' => 'nested'],

            'is_recto' => ['type' => 'boolean'],
            'is_verso' => ['type' => 'boolean'],
            'is_transversa_charta' => ['type' => 'boolean'],
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
            'material' => ['type' => self::FILTER_NESTED],
            'text_format' => ['type' => self::FILTER_NESTED],
            'writing_direction' => ['type' => self::FILTER_NESTED],
            'project' => ['type' => self::FILTER_NESTED],
            'production_stage' => ['type' => self::FILTER_NESTED],

            // copy from text
            'agentive_role' => [
                'type' => self::FILTER_NESTED,
                'nested_path' => 'agentive_role'
            ],
            'communicative_goal' => [
                'type' => self::FILTER_NESTED,
                'nested_path' => 'communicative_goal'
            ],
            'era' => ['type' => self::FILTER_NESTED],
            'generic_agentive_role' => [
                'type' => self::FILTER_NESTED,
                'nested_path' => 'agentive_role'
            ],
            'generic_communicative_goal' => [
                'type' => self::FILTER_NESTED,
                'nested_path' => 'communicative_goal'
            ],
            'date'=> [
                'type' => self::FILTER_DATE_RANGE,
                'floorField' => 'year_begin',
                'ceilingField' => 'year_end',
                'typeField' => 'date_search_type',
            ],
            'social_distance' => ['type' => self::FILTER_NESTED],
            'text_type' => ['type' => self::FILTER_NESTED],
            'text_subtype' => ['type' => self::FILTER_NESTED],
            'is_recto' => ['type' => self::FILTER_BOOLEAN],
            'is_verso' => ['type' => self::FILTER_BOOLEAN],
            'is_transversa_charta' => ['type' => self::FILTER_BOOLEAN],

            'lines' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'floorField' => 'lines_min',
                'ceilingField' => 'lines_max',
                'ignore' => [-1, 10000]
            ],
            'columns' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'floorField' => 'columns_min',
                'ceilingField' => 'columns_max',
                'ignore' => [-1, 10000]
            ],
            'letters_per_line' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'floorField' => 'letters_per_line_min',
                'ceilingField' => 'letters_per_line_max',
                'ignore' => [-1, 10000]
            ],
            'width' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'ignore' => [-1, 10000]
            ],
            'height' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'ignore' => [-1, 10000]
            ],
        ];

        // add extra filters if user role allows
        // ...

        return $searchFilters;
    }

    protected function getAggregationFilterConfig(): array {
        $aggregationFilters = [
            'material'  => ['type' => self::AGG_NESTED_ID_NAME],
            'text_format' => ['type' => self::AGG_NESTED_ID_NAME],
            'writing_direction' => ['type' => self::AGG_NESTED_ID_NAME],
            'production_stage' => ['type' => self::AGG_NESTED_ID_NAME],
            'is_recto' => ['type' => self::AGG_BOOLEAN],
            'is_verso' => ['type' => self::AGG_BOOLEAN],
            'is_transversa_charta' => ['type' => self::AGG_BOOLEAN],

            'agentive_role' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'agentive_role',
                'filter' => [ 'generic_agentive_role' => 'generic_agentive_role.id' ]
            ],
            'communicative_goal' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'communicative_goal',
                'filter' => [ 'generic_communicative_goal' => 'generic_communicative_goal.id' ]
            ],
            'era' => ['type' => self::AGG_NESTED_ID_NAME],
            'generic_agentive_role' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'agentive_role',
            ],
            'generic_communicative_goal' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'communicative_goal',
            ],
            'keyword' => ['type' => self::AGG_NESTED_ID_NAME],
            'language' => ['type' => self::AGG_NESTED_ID_NAME],
            'collaborator'  => ['type' => self::AGG_ID_NAME],
            'project'  => ['type' => self::AGG_NESTED_ID_NAME],
            'social_distance' => ['type' => self::AGG_NESTED_ID_NAME],
            'text_type' => ['type' => self::AGG_NESTED_ID_NAME],
            'text_subtype' => ['type' => self::AGG_NESTED_ID_NAME],

            'lines_max' => ['type' => self::AGG_GLOBAL_STATS],
            'lines_min' => ['type' => self::AGG_GLOBAL_STATS],
            'width' => ['type' => self::AGG_GLOBAL_STATS],
            'height' => ['type' => self::AGG_GLOBAL_STATS],
            'letters_per_line_min' => ['type' => self::AGG_GLOBAL_STATS],
            'letters_per_line_max' => ['type' => self::AGG_GLOBAL_STATS],
            'columns_min' => ['type' => self::AGG_GLOBAL_STATS],
            'columns_max' => ['type' => self::AGG_GLOBAL_STATS],
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
        $returnProps = ['id', 'tm_id', 'title', 'year_begin', 'year_end'];

        $result = array_intersect_key($result, array_flip($returnProps));

        return $result;
    }

    protected function sanitizeSearchParameters(array $params): array
    {
        if (isset($params['orderBy'])) {
            switch ($params['orderBy']) {
                // convert fieldname to elastic expression
                case 'title':
                    $params['orderBy'] = ['title.keyword'];

                    break;
                case 'year_begin':
                case 'year_end':
                    $params['orderBy'] = [ $params['orderBy'] ];
                    break;
                default:
                    unset($params['orderBy']);
                    break;
            }
        }

        return parent::sanitizeSearchParameters($params);
    }


}