<?php

namespace App\Service\ElasticSearch;

use Elastica\Mapping;
use Elastica\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AnnotationSearchService extends AbstractSearchService
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
            'text_format' => ['type' => self::FILTER_OBJECT_ID],
            'writing_direction' => ['type' => self::FILTER_NESTED_ID],
            'production_stage' => ['type' => self::FILTER_NESTED_ID],

            'agentive_role' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'agentive_role'
            ],
            'communicative_goal' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'communicative_goal'
            ],
            'era' => ['type' => self::FILTER_OBJECT_ID],
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
            'text_type' => ['type' => self::FILTER_OBJECT_ID],
            'text_subtype' => ['type' => self::FILTER_OBJECT_ID],

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

            /* ancient person */
            'ap_tm_id' => [
                'type' => self::FILTER_NUMERIC,
                'nested_path' => 'ancient_person',
                'field' => 'tm_id'
            ],
            'ap_role' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'ancient_person',
                'field' => 'role'
            ],
            'ap_gender' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'ancient_person',
                'field' => 'gender'
            ],
            'ap_occupation' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'ancient_person',
                'field' => 'occupation'
            ],
            'ap_social_rank' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'ancient_person',
                'field' => 'social_rank'
            ],
            'ap_honorific_epithet' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'ancient_person',
                'field' => 'honorific_epithet'
            ],
            'ap_graph_type' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'ancient_person',
                'field' => 'graph_type'
            ],

        ];

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

        $searchFilters['annotations'] = [
            'type' => self::FILTER_NESTED_MULTIPLE,
            'nested_path' => 'annotations',
            'filters' => [
                'annotation_type' => [
                    'field' => 'type.keyword'
                ]
            ],
            'innerHits' => true
        ];

        foreach( $annotationFilters as $type => $filters ) {
            foreach( $filters as $filter ) {
                $subfilter_name = "{$type}_{$filter}";
                $subfilter_field = "{$type}_{$filter}";
                $searchFilters['annotations']['filters'][$subfilter_name] = [
                    'field' => "properties.{$subfilter_field}.id",
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

            'era' => ['type' => self::AGG_OBJECT_ID_NAME],

            /* role */
            'agentive_role' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'agentive_role',
                'filter' => [ 'generic_agentive_role' => 'generic_agentive_role.id' ]
            ],
            'generic_agentive_role' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'agentive_role',
            ],
            /* goal */
            'communicative_goal' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'communicative_goal',
                'filter' => [ 'generic_communicative_goal' => 'generic_communicative_goal.id' ]
            ],
            'generic_communicative_goal' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'communicative_goal',
            ],

            'archive' => ['type' => self::AGG_OBJECT_ID_NAME],

            'keyword' => ['type' => self::AGG_NESTED_ID_NAME],
            'language' => ['type' => self::AGG_NESTED_ID_NAME],
            'collaborator'  => ['type' => self::AGG_OBJECT_ID_NAME],
            'project'  => ['type' => self::AGG_NESTED_ID_NAME],
            'social_distance' => ['type' => self::AGG_NESTED_ID_NAME],
            'text_type' => ['type' => self::AGG_OBJECT_ID_NAME],
            'text_subtype' => ['type' => self::AGG_OBJECT_ID_NAME],
            'form'  => ['type' => self::AGG_NESTED_ID_NAME],
            'location_written' => ['type' => self::AGG_NESTED_ID_NAME],
            'location_found' => ['type' => self::AGG_NESTED_ID_NAME],
            'script' => ['type' => self::AGG_NESTED_ID_NAME],

            /* global stats */
            'lines_max' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'lines.max'],
            'lines_min' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'lines.min'],
            'width' => ['type' => self::AGG_GLOBAL_STATS],
            'height' => ['type' => self::AGG_GLOBAL_STATS],
            'letters_per_line_min' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'letters_per_line.min'],
            'letters_per_line_max' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'letters_per_line.max'],
            'columns_min' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'columns_min'],
            'columns_max' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'columns_max'],

            /* ancient person */
            'ap_name' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => ''
            ],
            'ap_role' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => 'role'
            ],
            'ap_gender' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => 'gender'
            ],
            'ap_occupation' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => 'occupation'
            ],
            'ap_social_rank' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => 'social_rank'
            ],
            'ap_honorific_epithet' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => 'honorific_epithet'
            ],
            'ap_graph_type' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => 'graph_type'
            ],
        ];

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

        // create aggregation filter keys (typography_wordSplitting, typography_correction ...)
        $annotationFilterKeys = array_reduce(
            array_keys($annotationFilters),
            function($carry, $type) use ($annotationFilters) { return array_merge($carry, preg_filter('/^/', "{$type}_", $annotationFilters[$type])); },
            []
        );
        //$annotationFilterKeys[] = "annotation_type";

        // add annotation property filters
        foreach( $annotationFilters as $type => $filters ) {
            foreach( $filters as $filter ) {
                $filter_name = "{$type}_{$filter}";
                $property_name = "{$type}_{$filter}";
                $aggregationFilters[$filter_name] = [
                    'type' => self::AGG_NESTED_ID_NAME,
                    'field' => "properties.{$property_name}",
                    'nested_path' => "annotations",
                    'excludeFilter' => ['annotations'], // exclude filter of same type
                    'filter' => array_reduce( $annotationFilterKeys, function($carry,$subfilter_name) use ($type,$filter_name) {
                        if ( $subfilter_name != $filter_name ) {
                            $carry[$subfilter_name] = "properties.{$subfilter_name}.id";
                        }
                        return $carry;
                    }, [])
                ];
                $aggregationFilters[$filter_name]['filter']['annotation_type'] = "type";
            }
        }

        // add annotation type filter
        $aggregationFilters['annotation_type'] = [
            'type' => self::AGG_KEYWORD,
            'field' => 'type',
            'nested_path' => "annotations",
            'excludeFilter' => ['annotations'], // exclude filter of same type
            'filter' => array_reduce( $annotationFilterKeys, function($carry,$subfilter_name) use ($type,$filter_name) {
                if ( $subfilter_name != $filter_name ) {
                    $carry[$subfilter_name] = "properties.{$subfilter_name}.id";
                }
                return $carry;
            }, []),
        ];

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
        $returnProps = ['id', 'tm_id', 'title', 'year_begin', 'year_end', 'inner_hits', 'annotations'];

        $result = array_intersect_key($result, array_flip($returnProps));
        $result['annotations'] = $result['annotations'] ?? [];
        if ( isset($result['inner_hits']['annotations']) ) {
            $result['annotations'] = $result['inner_hits']['annotations'];
        }

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