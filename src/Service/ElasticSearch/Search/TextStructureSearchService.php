<?php

namespace App\Service\ElasticSearch\Search;

use App\Service\ElasticSearch\Client;
use Elastica\Settings;

class TextStructureSearchService extends AbstractSearchService
{
    const indexName = "level";

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
            Configs::filterMateriality(),
            Configs::filterAttestations(),
            Configs::filterAdministrative(),
        );

        $searchFilters['textLevel'] = [
            'field' => 'number',
            'type' => self::FILTER_NUMERIC,
            'aggregationFilter' => true,
        ];

        $searchFilters['annotation_type'] = [
            'field' => 'annotations.type',
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
            'handshift' => ['abbreviation','accentuation','connectivity','correction','curvature','degreeOfFormality','expansion','lineation','orientation','punctuation','regularity','scriptType','slope','wordSplitting', 'status'],
        ];

        foreach( $annotationProperties as $type => $properties ) {
            foreach( $properties as $property ) {
                $subfilter_name = "{$type}_{$property}";
                $subfilter_field = "{$type}_{$property}";
                $searchFilters['annotations']['filters'][$subfilter_name] = [
                    'field' => "annotations.properties.{$subfilter_field}",
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
            Configs::aggregateAttestations(),
            Configs::aggregateAdministrative(),
        );

        // annotation aggregations
        $annotationProperties = [
            'gts' => ['part'],
            'lts' => ['part'],
            'ltsa' => ['type', 'subtype', 'spacing', 'separation', 'orientation', 'alignment', 'indentation', 'lectionalSigns', 'lineation', 'pagination'],
            'gtsa' => ['type', 'subtype', 'standardForm', 'attachedTo', 'attachmentType','speechAct','informationStatus'],
            'handshift' => ['abbreviation','accentuation','connectivity','correction','curvature','degreeOfFormality','expansion','lineation','orientation','punctuation','regularity','scriptType','slope','wordSplitting', 'status'],
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
                $field_name = "annotations.properties.{$type}_{$property}";
                $aggregationFilters[$filter_name] = [
                    'type' => self::AGG_NESTED_ID_NAME,
                    'field' => $field_name,
                    'nested_path' => "annotations",
//                    'excludeFilter' => ['annotations'], // exclude filter of same type
//                    'filters' => array_reduce( $annotationFilterKeys, function($carry,$subfilter_name) use ($filter_name) {
//                        if ( $subfilter_name != $filter_name ) {
//                            $carry[$subfilter_name] = [
//                                'field' => "annotations.properties.{$subfilter_name}",
//                                'type' => self::FILTER_OBJECT_ID
//                            ];
//                        }
//                        return $carry;
//                    }, [])
                ];
            }
        }

        // add level number aggretation
        $filter_name = 'textLevel';
        $aggregationFilters[$filter_name] = [
            'type' => self::AGG_NUMERIC,
            'field' => 'number',
        ];

        // filter annotation_type
        $aggregationFilters[$filter_name]['filters']['annotation_type'] = [
            'field' => 'annotations.type',
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
        $returnProps = ['id', 'text_id', 'tm_id', 'title', 'year_begin', 'year_end', 'inner_hits', 'annotations', 'level_category', 'location_found'];

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
                case 'text_id':
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