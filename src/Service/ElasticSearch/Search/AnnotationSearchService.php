<?php

namespace App\Service\ElasticSearch\Search;

use App\Service\ElasticSearch\Client;
use Elastica\Settings;

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
        $searchFilters = array_merge(
            Configs::filterPhysicalInfo(),
            Configs::filterCommunicativeInfo(),
            Configs::filterMateriality(),
            Configs::filterAncientPerson(),
            Configs::filterAdministrative(),
        );

        // build annotation filters
        // 1. add annotation type filter
        // 2. add property filters

        $searchFilters['annotations'] = [
            'type' => self::FILTER_NESTED_MULTIPLE,
            'nested_path' => 'annotations',
            'filters' => [
                'annotation_type' => [
                    'field' => 'type',
                    'type' => self::FILTER_KEYWORD
                ],
                'text_level' => [
                    'field' => 'properties.textLevel.number',
                    'type' => self::FILTER_NUMERIC
                ],
            ],
            'innerHits' => [
                'size' => 100
            ]
        ];

        $annotationProperties = [
            'typography' => ['wordSplitting','correction','insertion', 'abbreviation', 'deletion', 'symbol', 'punctuation', 'accentuation', 'vacat', 'accronym', 'positionInText', 'wordClass'],
            'lexis' => ['standardForm','type','subtype','wordclass','formulaicity','prescription','proscription','identifier'],
            'orthography' => ['standardForm','type','subtype','wordclass','formulaicity','positionInWord'],
            'morphology' => ['standardForm','type','subtype','wordclass','formulaicity','positionInWord'],
            'language' => ['bigraphismDomain', 'bigraphismRank', 'bigraphismFormulaicity', 'bigraphismType', 'codeswitchingType' ],
            'morpho_syntactical' => [
                'coherenceForm', 'coherenceContent', 'coherenceContext',
                'complementationForm', 'complementationContent', 'complementationContext',
                'subordinationForm', 'subordinationContent', 'subordinationContext',
//                'relativisationForm', 'relativisationContent', 'relativisationContext',
                'orderForm', 'orderContent', 'orderContext', // todo: replace by relativisation fields after reimport
                'typeFormulaicity'
            ],
            'handshift' => ['abbreviation','accentuation','connectivity','correction','curvature','degreeOfFormality','expansion','lineation','orientation','punctuation','regularity','scriptType','slope','wordSplitting', 'status'],
            'gts' => ['part'],
            'gtsa' => ['type', 'subtype', 'speechAct'],
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
        $aggregationFilters = array_merge(
            Configs::aggregatePhysicalInfo(),
            Configs::aggregateCommunicativeInfo(),
            Configs::aggregateMateriality(),
            Configs::aggregateAncientPerson(),
            Configs::aggregateAdministrative(),
        );

        // annotation aggregations
        $annotationProperties = [
            'typography' => ['wordSplitting','correction','insertion', 'abbreviation', 'deletion', 'symbol', 'punctuation', 'accentuation', 'vacat', 'accronym', 'positionInText', 'wordClass'],
            'lexis' => ['standardForm','type','subtype','wordclass','formulaicity','prescription','proscription','identifier'],
            'orthography' => ['standardForm','type','subtype','wordclass','formulaicity','positionInWord'],
            'morphology' => ['standardForm','type','subtype','wordclass','formulaicity','positionInWord'],
            // rank opsplitsen
            'language' => ['bigraphismDomain', 'bigraphismRank', 'bigraphismFormulaicity', 'bigraphismType', 'codeswitchingType' ],
            'morpho_syntactical' => [
                'coherenceForm', 'coherenceContent', 'coherenceContext',
                'complementationForm', 'complementationContent', 'complementationContext',
                'subordinationForm', 'subordinationContent', 'subordinationContext',
//                'relativisationForm', 'relativisationContent', 'relativisationContext', // todo: enable after schema update
                'orderForm', 'orderContent', 'orderContext', // todo: remove after schema update
                'typeFormulaicity'
            ],
            'handshift' => ['abbreviation','accentuation','connectivity','correction','curvature','degreeOfFormality','expansion','lineation','orientation','punctuation','regularity','scriptType','slope','wordSplitting', 'status'],
            'gts' => ['part'],
            'gtsa' => ['type', 'subtype', 'speechAct'],
        ];

        // create aggregation filter keys (typography_wordSplitting, typography_correction ...)
        $annotationFilterKeys = array_reduce(
            array_keys($annotationProperties),
            function($carry, $type) use ($annotationProperties) { return array_merge($carry, preg_filter('/^/', "{$type}_", $annotationProperties[$type])); },
            []
        );
        //$annotationFilterKeys[] = "annotation_type";

        // add annotation property filters
        foreach( $annotationProperties as $type => $properties ) {
            foreach( $properties as $property ) {
                $filter_name = "{$type}_{$property}";
                $field_name = "properties.{$type}_{$property}";
                $aggregationFilters[$filter_name] = [
                    'type' => self::AGG_NESTED_ID_NAME,
                    'field' => $field_name,
                    'nested_path' => "annotations",
//                    'excludeFilter' => ['annotations'], // exclude filter of same type
//                    'filters' => array_reduce( $annotationFilterKeys, function($carry,$subfilter_name) use ($type,$filter_name) {
//                        if ( $subfilter_name != $filter_name ) {
//                            $carry[$subfilter_name] = [
//                                'field' => "properties.{$subfilter_name}",
//                                'type' => self::FILTER_OBJECT_ID
//                            ];
//                        }
//                        return $carry;
//                    }, [])
                ];
//                // filter on type
//                $aggregationFilters[$filter_name]['filters']['annotation_type'] = [
//                    'field' => 'type',
//                    'type' => self::FILTER_KEYWORD
//                ];
//                // filter on generic text structure part
//                $aggregationFilters[$filter_name]['filters']['generic_text_structure_part'] = [
//                    'field' => 'properties.gts_part',
//                    'type' => self::FILTER_OBJECT_ID
//                ];
//                // filter on text level
//                $aggregationFilters[$filter_name]['filters']['text_level'] = [
//                    'field' => 'properties.textLevel.number',
//                    'type' => self::FILTER_NUMERIC
//                ];
            }
        }

        // add annotation type filter
        $aggregationFilters['annotation_type'] = [
            'type' => self::AGG_KEYWORD,
            'field' => 'type',
            'nested_path' => "annotations",
//            'excludeFilter' => ['annotations'], // exclude filter of same type
//            'filters' => array_reduce( $annotationFilterKeys, function($carry,$subfilter_name) use ($type,$filter_name) {
//                if ( $subfilter_name != $filter_name ) {
//                    $carry[$subfilter_name] = [
//                        'field' => "properties.{$subfilter_name}",
//                        'type' => self::FILTER_OBJECT_ID
//                    ];
//                }
//                return $carry;
//            }, []),
            'replaceLabel' => [
                'search' => 'morpho_syntactical',
                'replace' => 'syntax'
            ]
        ];

        // add annotation type filter
        $aggregationFilters['text_level'] = [
            'type' => self::AGG_NUMERIC,
            'field' => 'properties.textLevel.number',
            'nested_path' => "annotations",
//            'excludeFilter' => ['annotations'], // exclude filter of same type
//            'filters' => array_reduce( $annotationFilterKeys, function($carry,$subfilter_name) use ($type,$filter_name) {
//                if ( $subfilter_name != $filter_name ) {
//                    $carry[$subfilter_name] = "properties.{$subfilter_name}.id";
//                }
//                return $carry;
//            }, []),
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
        $returnProps = ['id', 'tm_id', 'title', 'year_begin', 'year_end', 'inner_hits', 'annotations', 'level_category', 'location_found'];

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
                case 'id':
                case 'tm_id':
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