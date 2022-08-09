<?php

namespace App\Service\ElasticSearch;

use Elastica\Mapping;
use Elastica\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TextStructureSearchService extends AbstractSearchService
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
        $searchFilters = [
            'title' => [
                'type' => self::FILTER_KEYWORD,
                'field' => 'title.keyword'
            ],
            'id' => ['type' => self::FILTER_NUMERIC],
            'tm_id' => ['type' => self::FILTER_NUMERIC],
            'archive' => ['type' => self::FILTER_OBJECT_ID],

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
            'script' => ['type' => self::FILTER_NESTED_ID],
            'location_written' => ['type' => self::FILTER_NESTED_ID],
            'location_found' => ['type' => self::FILTER_NESTED_ID],
            'project' => ['type' => self::FILTER_NESTED_ID],
            'collaborator' => ['type' => self::FILTER_OBJECT_ID],
            'social_distance' => ['type' => self::FILTER_NESTED_ID],
            'text_type' => ['type' => self::FILTER_OBJECT_ID],
            'text_subtype' => ['type' => self::FILTER_OBJECT_ID],

            /* materiality */
            'production_stage' => ['type' => self::FILTER_NESTED_ID],
            'material' => ['type' => self::FILTER_NESTED_ID],
            'text_format' => ['type' => self::FILTER_NESTED_ID],
            'writing_direction' => ['type' => self::FILTER_NESTED_ID],
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

        $searchFilters['annotation_type'] = [
            'field' => 'type.keyword',
            'nested_path' => 'annotations',
            'type' => self::FILTER_KEYWORD,
            'defaultValue' => ['gts', 'gtsa', 'lts', 'ltsa'],
        ];

        // build annotation filters
        // 1. add annotation type filter
        // 2. add property filters

        $searchFilters['annotations'] = [
            'type' => self::FILTER_NESTED_MULTIPLE,
            'nested_path' => 'annotations',
            'filters' => [
                'gts_textLevel' => [
                    'field' => 'properties.gts_textLevel.number',
                    'type' => self::FILTER_NUMERIC,
                ],
            ],
            'innerHits' => [
                'size' => 100,
            ],
        ];

        $annotationProperties = [
            'gts' => ['part'],
            'lts' => ['part'],
            'ltsa' => ['type', 'subtype', 'spacing', 'separation', 'orientation', 'alignment', 'indentation', 'lectionalSigns', 'lineation', 'pagination'],
            'gtsa' => ['type', 'subtype', 'standardForm', 'attachedTo', 'attachmentType','speechAct','informationStatus'],
            'handshift' => ['abbreviation','accentuation','connectivity','correction','curvature','degreeOfFormality','expansion','lineation','orientation','punctuation','regularity','scriptType','slope','wordSplitting'],
        ];

        foreach( $annotationProperties as $type => $properties ) {
            foreach( $properties as $property ) {
                $subfilter_name = "{$type}_{$property}";
                $subfilter_field = "{$type}_{$property}";
                $searchFilters['annotations']['filters'][$subfilter_name] = [
                    'field' => "properties.{$subfilter_field}",
                    'type' => self::FILTER_OBJECT_ID
                ];
            }
        }

        return $searchFilters;
    }

    protected function getAggregationConfig(): array {
        $aggregationFilters = [
            'era' => ['type' => self::AGG_OBJECT_ID_NAME],

            /* role */
            'agentive_role' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'agentive_role',
                'filter' => [ 'generic_agentive_role' => 'generic_agentive_role.id' ]
            ],
            'generic_agentive_role' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'agentive_role',
            ],
            /* goal */
            'communicative_goal' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'communicative_goal',
                'filter' => [ 'generic_communicative_goal' => 'generic_communicative_goal.id' ]
            ],
            'generic_communicative_goal' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'communicative_goal',
            ],

            'archive' => ['type' => self::AGG_OBJECT_ID_NAME],

            'form'  => ['type' => self::AGG_NESTED_ID_NAME],
            'keyword' => ['type' => self::AGG_NESTED_ID_NAME],
            'language' => ['type' => self::AGG_NESTED_ID_NAME],
            'script' => ['type' => self::AGG_NESTED_ID_NAME],
            'location_written' => ['type' => self::AGG_NESTED_ID_NAME],
            'location_found' => ['type' => self::AGG_NESTED_ID_NAME],
            'material'  => ['type' => self::AGG_NESTED_ID_NAME],
            'collaborator'  => ['type' => self::AGG_OBJECT_ID_NAME],
            'project'  => [
                'type' => self::AGG_NESTED_ID_NAME,
                'limitId' => [2,3,4,9] //todo: fix this!!
            ],
            'social_distance' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'ignoreValue' => self::ignoreUnknownUncertain,
            ],
            'text_type' => ['type' => self::AGG_OBJECT_ID_NAME],
            'text_subtype' => ['type' => self::AGG_OBJECT_ID_NAME],

            // materiality
            'text_format' => ['type' => self::AGG_NESTED_ID_NAME],
            'writing_direction' => ['type' => self::AGG_NESTED_ID_NAME],
            'production_stage' => ['type' => self::AGG_NESTED_ID_NAME],
            'is_recto' => ['type' => self::AGG_BOOLEAN],
            'is_verso' => ['type' => self::AGG_BOOLEAN],
            'is_transversa_charta' => ['type' => self::AGG_BOOLEAN],
            'lines_max' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'lines.max'],
            'lines_min' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'lines.min'],
            'width' => ['type' => self::AGG_GLOBAL_STATS],
            'height' => ['type' => self::AGG_GLOBAL_STATS],
            'letters_per_line_min' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'letters_per_line.min'],
            'letters_per_line_max' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'letters_per_line.max'],
            'columns_min' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'columns_min'],
            'columns_max' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'columns_max'],

        ];

        // annotation aggregations
        $annotationProperties = [
            'gts' => ['part'],
            'lts' => ['part'],
            'ltsa' => ['type', 'subtype', 'spacing', 'separation', 'orientation', 'alignment', 'indentation', 'lectionalSigns', 'lineation', 'pagination'],
            'gtsa' => ['type', 'subtype', 'standardForm', 'attachedTo', 'attachmentType','speechAct','informationStatus'],
            'handshift' => ['abbreviation','accentuation','connectivity','correction','curvature','degreeOfFormality','expansion','lineation','orientation','punctuation','regularity','scriptType','slope','wordSplitting'],
        ];

        // create aggregation filter keys (typography_wordSplitting, typography_correction ...)
        $annotationFilterKeys = array_reduce(
            array_keys($annotationProperties),
            function($carry, $type) use ($annotationProperties) { return array_merge($carry, preg_filter('/^/', "{$type}_", $annotationProperties[$type])); },
            []
        );

        // add annotation property filters
        foreach( $annotationProperties as $type => $properties ) {
            foreach( $properties as $property ) {
                $filter_name = "{$type}_{$property}";
                $field_name = "properties.{$type}_{$property}";
                $aggregationFilters[$filter_name] = [
                    'type' => self::AGG_NESTED_ID_NAME,
                    'field' => $field_name,
                    'nested_path' => "annotations",
                    'excludeFilter' => ['annotations'], // exclude filter of same type
                    'filter' => array_reduce( $annotationFilterKeys, function($carry,$subfilter_name) use ($filter_name) {
                        if ( $subfilter_name != $filter_name ) {
                            $carry[$subfilter_name] = [
                                'field' => "properties.{$subfilter_name}",
                                'type' => self::FILTER_OBJECT_ID
                            ];
                        }
                        return $carry;
                    }, [])
                ];
                // filter on text level
                $aggregationFilters[$filter_name]['filter']['gts_textLevel'] = [
                    "properties.gts_textLevel.number",
                    'type' => self::FILTER_NUMERIC
                ];
            }
        }

        // add annotation type aggretation
        $filter_name = 'gts_textLevel';
        $aggregationFilters[$filter_name] = [
            'type' => self::AGG_KEYWORD,
            'field' => 'type',
            'nested_path' => "annotations",
            'excludeFilter' => ['annotations'], // exclude filter of same type
            'filter' => array_reduce( $annotationFilterKeys, function($carry,$subfilter_name) use ($filter_name) {
                if ( $subfilter_name != $filter_name ) {
                    $carry[$subfilter_name] = [
                        'field' => "properties.{$subfilter_name}",
                        'type' => self::FILTER_OBJECT_ID
                    ];
                }
                return $carry;
            }, []),
        ];
        // filter annotation_type
        $aggregationFilters[$filter_name]['filter']['annotation_type'] = [
            'field' => 'type',
            'type' => self::FILTER_KEYWORD
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
        $returnProps = ['id', 'tm_id', 'title', 'year_begin', 'year_end', 'inner_hits', 'annotations', 'text_type', 'location_found'];

        $result = array_intersect_key($result, array_flip($returnProps));
        $result['annotations'] = $result['annotations'] ?? [];
        if ( isset($result['inner_hits']['annotations']) ) {
            $result['annotations'] = $result['inner_hits']['annotations'];
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
                case 'year_begin':
                case 'year_end':
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