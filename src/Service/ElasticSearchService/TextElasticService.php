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
            'year_begin' => ['type' => 'short'],
            'year_end' => ['type' => 'short'],
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
            'agentive_role' => ['type' => 'nested'],
            'communicative_goal'  => ['type' => 'nested'],
            'attestation_education'  => ['type' => 'nested'],
            'attestation_age'  => ['type' => 'nested'],
            'attestation_graph_type'  => ['type' => 'nested'],
            'ancient_person' => [
                'type' => 'nested',
//                'properties' => [
//                    'role' => [
//                        'type' => 'nested'
//                    ]
//                ]
            ]
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
            'form' => ['type' => self::FILTER_NESTED],
            'keyword' => ['type' => self::FILTER_NESTED],
            'language' => ['type' => self::FILTER_NESTED],
            'location_written' => ['type' => self::FILTER_NESTED],
            'location_found' => ['type' => self::FILTER_NESTED],
            'material' => ['type' => self::FILTER_NESTED],
            'project' => ['type' => self::FILTER_NESTED],
            'collaborator' => ['type' => self::FILTER_ID],
            'social_distance' => ['type' => self::FILTER_NESTED],
            'text_type' => ['type' => self::FILTER_NESTED],
            'text_subtype' => ['type' => self::FILTER_NESTED],

            'ap_tm_id' => [
                'type' => self::FILTER_NUMERIC,
                'nested_path' => 'ancient_person',
                'field' => 'tm_id'
            ],
            'ap_role' => [
                'type' => self::FILTER_NESTED,
                'nested_path' => 'ancient_person',
                'field' => 'role'
            ],
            'ap_gender' => [
                'type' => self::FILTER_NESTED,
                'nested_path' => 'ancient_person',
                'field' => 'gender'
            ],
            'ap_occupation' => [
                'type' => self::FILTER_NESTED,
                'nested_path' => 'ancient_person',
                'field' => 'occupation'
            ],
            'ap_social_rank' => [
                'type' => self::FILTER_NESTED,
                'nested_path' => 'ancient_person',
                'field' => 'social_rank'
            ],
            'ap_honorific_epithet' => [
                'type' => self::FILTER_NESTED,
                'nested_path' => 'ancient_person',
                'field' => 'honorific_epithet'
            ],
            'ap_graph_type' => [
                'type' => self::FILTER_NESTED,
                'nested_path' => 'ancient_person',
                'field' => 'graph_type'
            ],

        ];

        // add extra filters if user role allows
        // ...

        return $searchFilters;
    }

    protected function getAggregationFilterConfig(): array {
        $aggregationFilters = [
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
            'archive' => ['type' => self::AGG_NESTED_ID_NAME],
            'era' => ['type' => self::AGG_NESTED_ID_NAME],
            'generic_agentive_role' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'agentive_role',
            ],
            'generic_communicative_goal' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'communicative_goal',
            ],
            'form'  => ['type' => self::AGG_NESTED_ID_NAME],
            'keyword' => ['type' => self::AGG_NESTED_ID_NAME],
            'language' => ['type' => self::AGG_NESTED_ID_NAME],
            'location_written' => ['type' => self::AGG_NESTED_ID_NAME],
            'location_found' => ['type' => self::AGG_NESTED_ID_NAME],
            'material'  => ['type' => self::AGG_NESTED_ID_NAME],
            'collaborator'  => ['type' => self::AGG_ID_NAME],
            'project'  => ['type' => self::AGG_NESTED_ID_NAME],
            'script' => ['type' => self::AGG_NESTED_ID_NAME],
            'social_distance' => ['type' => self::AGG_NESTED_ID_NAME],
            'text_type' => ['type' => self::AGG_NESTED_ID_NAME],
            'text_subtype' => ['type' => self::AGG_NESTED_ID_NAME],

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
