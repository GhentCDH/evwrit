<?php

namespace App\Service\ElasticSearch;

use Elastica\Mapping;
use Elastica\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TextStructureSearchService extends AbstractSearchService
{
    const indexName = "texts";

    public function __construct(Client $client)
    {
        parent::__construct(
            $client,
            self::indexName);
    }

    protected function getSearchFilterConfig(): array {
        $searchFilters = [];

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
            'ltsa' => ['type', 'subType', 'spacing', 'separation', 'orientation', 'alignment', 'indentation', 'lectionalSigns', 'lineation', 'pagination'],
            'gtsa' => ['type', 'subType', 'standardForm', 'attachedTo', 'attachmentType','speechAct','informationStatus'],
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
            'language'  => ['type' => self::AGG_NESTED_ID_NAME],
        ];

        // annotation aggregations
        $annotationProperties = [
            'gts' => ['part'],
            'lts' => ['part'],
            'ltsa' => ['type', 'subType', 'spacing', 'separation', 'orientation', 'alignment', 'indentation', 'lectionalSigns', 'lineation', 'pagination'],
            'gtsa' => ['type', 'subType', 'standardForm', 'attachedTo', 'attachmentType','speechAct','informationStatus'],
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
        $returnProps = ['id', 'tm_id', 'title', 'year_begin', 'year_end', 'inner_hits', 'annotations'];

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