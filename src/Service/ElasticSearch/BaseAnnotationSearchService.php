<?php

namespace App\Service\ElasticSearch;

use Elastica\Mapping;
use Elastica\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseAnnotationSearchService extends AbstractSearchService
{
    const indexName = "texts";

    public function __construct(Client $client)
    {
        parent::__construct(
            $client,
            self::indexName);
    }

    protected function getSearchFilterConfig(): array {
        $searchFilters = [
            'title' => ['type' => self::FILTER_TEXT],
            'id' => ['type' => self::FILTER_NUMERIC],
            'tm_id' => ['type' => self::FILTER_NUMERIC],
            'archive' => ['type' => self::FILTER_NESTED_ID],
            'text_format' => ['type' => self::FILTER_NESTED_ID],
            'writing_direction' => ['type' => self::FILTER_NESTED_ID],
            'production_stage' => ['type' => self::FILTER_NESTED_ID],

            // copy from text
            'agentive_role' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'agentive_role'
            ],
            'communicative_goal' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'communicative_goal'
            ],
            'era' => ['type' => self::FILTER_NESTED_ID],
            'generic_agentive_role' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'agentive_role'
            ],
            'generic_communicative_goal' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'communicative_goal'
            ],
            'date'=> [
                'type' => self::FILTER_DATE_RANGE,
                'floorField' => 'year_begin',
                'ceilingField' => 'year_end',
                'typeField' => 'date_search_type',
            ],

            'form' => ['type' => self::FILTER_NESTED_ID],
            'keyword' => ['type' => self::FILTER_NESTED_ID],
            'language' => ['type' => self::FILTER_NESTED_ID],
            'location_written' => ['type' => self::FILTER_NESTED_ID],
            'location_found' => ['type' => self::FILTER_NESTED_ID],
            'material' => ['type' => self::FILTER_NESTED_ID],
            'project' => ['type' => self::FILTER_NESTED_ID],
            'collaborator' => ['type' => self::FILTER_OBJECT_ID],
            'social_distance' => ['type' => self::FILTER_NESTED_ID],
            'text_type' => ['type' => self::FILTER_NESTED_ID],
            'text_subtype' => ['type' => self::FILTER_NESTED_ID],

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

        $searchFilters = [];

        // annotation filters
        $annotationFilters = [
            'typography' => ['wordSplitting','correction','insertion', 'abbreviation', 'deletion', 'symbol', 'punctuation', 'accentuation'],
            'lexis' => ['standardForm','type','subtype','wordclass','formulaicity','prescription','proscription','identifier'],
            'orthography' => ['standardForm','type','subtype','wordclass','formulaicity','positionInWord'],
            'language' => ['bigraphismDomain', 'bigraphismRank', 'bigraphismFormulaicity', 'bigraphismType', 'codeswitchingType' ],
            'morpho_syntactical' => [
                'coherenceForm', 'coherenceContent', 'coherenceContext',
                'complementationForm', 'complementationContent', 'complementationContext',
                'subordinationForm', 'subordinationContent', 'subordinationContext',
                'relativisationForm', 'relativisationContent', 'relativisationContext',
                'typeFormulaicity'
            ]
        ];
        foreach( $annotationFilters as $type => $filters ) {
            $searchFilters[$type] = [
                'type' => self::FILTER_NESTED_MULTIPLE,
                'nested_path' => "annotations.{$type}",
                'filters' => [],
                'innerHits' => true
            ];
            foreach( $filters as $filter ) {
                $filter_name = "{$type}_{$filter}";
                $searchFilters[$type]['filters'][$filter_name] = [
                    'field' => "properties.{$filter}",
                ];
            }
        }

        return $searchFilters;
    }

    protected function getAggregationConfig(): array {
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
            'collaborator'  => ['type' => self::AGG_OBJECT_ID_NAME],
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

        $aggregationFilters = [];

        // annotation aggregations
        $annotationFilters = [
            'typography' => ['wordSplitting','correction','insertion', 'abbreviation', 'deletion', 'symbol', 'punctuation', 'accentuation'],
            'lexis' => ['standardForm','type','subtype','wordclass','formulaicity','prescription','proscription','identifier'],
            'orthography' => ['standardForm','type','subtype','wordclass','formulaicity','positionInWord'],
            // rank opsplitsen
            'language' => ['bigraphismDomain', 'bigraphismRank', 'bigraphismFormulaicity', 'bigraphismType', 'codeswitchingType' ],
            'morpho_syntactical' => [
                'coherenceForm', 'coherenceContent', 'coherenceContext',
                'complementationForm', 'complementationContent', 'complementationContext',
                'subordinationForm', 'subordinationContent', 'subordinationContext',
                'relativisationForm', 'relativisationContent', 'relativisationContext',
                'typeFormulaicity'
            ]
        ];
        foreach( $annotationFilters as $type => $filters ) {
            foreach( $filters as $filter ) {
                $filter_name = "{$type}_{$filter}";
                $aggregationFilters[$filter_name] = [
                    'type' => self::AGG_NESTED_ID_NAME,
                    'field' => "properties.{$filter}",
                    'nested_path' => "annotations.{$type}",
                    'excludeFilter' => [$type], // exclude filter of same type
                    'filter' => array_reduce( $filters, function($carry,$item) use ($type,$filter) {
                        if ( $item != $filter ) {
                            $carry["{$type}_{$item}"] = "properties.{$item}.id";
                        }
                        return $carry;
                    }, [])
                ];
            }
        }
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
        $returnProps = ['id', 'tm_id', 'title', 'year_begin', 'year_end', 'inner_hits'];

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
