<?php

namespace App\Service\ElasticSearchService;

use Elastica\Mapping;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TextElasticService extends ElasticEntityService
{
    public function __construct(ElasticSearchClient $client, ContainerInterface $container)
    {
        parent::__construct(
            $client,
            'texts',
            'text'
//            $container->get('identifier_manager')->getByType('person')
        );
    }

    public function setup(): void
    {
        $index = $this->getIndex();
        if ($index->exists()) {
            $index->delete();
        }
        // Configure analysis
        $index->create(Analysis::ANALYSIS);

        $mapping = new Mapping;
        $mapping->setProperties(
            [
                'name' => [
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
            ]
        );
        $mapping->send($this->getIndex());
    }

    public function searchAndAggregate(array $params, bool $viewInternal): array
    {
        // search
        if (!empty($params['filters'])) {
            $params['filters'] = $this->classifySearchFilters($params['filters'], $viewInternal);
        }
        $result = $this->search($params);

        // aggregate
        $aggregationFilters = ['historical', 'modern', 'role', 'office', 'self_designation', 'origin'];
        if ($viewInternal) {
            // add internal filters ...
        }

        $result['aggregation'] = $this->aggregate(
            $this->classifyAggregationFilters(array_merge($this->getIdentifierSystemNames(), $aggregationFilters), $viewInternal),
            !empty($params['filters']) ? $params['filters'] : []
        );

        // Add 'no-selectors' for primary identifiers
        if ($viewInternal) {
            foreach ($this->primaryIdentifiers as $identifier) {
                $result['aggregation'][$identifier->getSystemName()][] = [
                    'id' => -1,
                    'name' => 'No ' . $identifier->getName(),
                ];
            }
        }

        return $result;
    }

    /**
     * Add elasticsearch information to aggregation filters
     * @param  array  $filters
     * @param  bool   $viewInternal indicates whether internal (non-public) data can be displayed
     * @return array
     */
    public function classifyAggregationFilters(array $filters, bool $viewInternal): array
    {
        $result = [];
        foreach ($filters as $key => $value) {
            // Primary identifiers
            if (in_array($value, $this->getIdentifierSystemNames())) {
                $result['exact_text'][] = $value;
                continue;
            }

            switch ($value) {
            case 'role':
            case 'self_designation':
            case 'office':
            case 'origin':
            case 'management':
                $result['nested'][] = $value;
                break;
            case 'public':
            case 'historical':
            case 'modern':
                $result['boolean'][] = $value;
                break;
            }
        }
        return $result;
    }

    /**
     * Add elasticsearch information to search filters
     * @param  array $filters
     * @param  bool   $viewInternal indicates whether internal (non-public) data can be displayed
     * @return array
     */
    public function classifySearchFilters(array $filters, bool $viewInternal): array
    {
        $result = [];
        foreach ($filters as $key => $value) {
            if (!isset($value) || $value === '') {
                continue;
            }

            // Primary identifiers
            if (in_array($key, $this->getIdentifierSystemNames())) {
                $result['exact_text'][$key] = $value;
                continue;
            }

            switch ($key) {
            case 'date':
                $date_result = [
                    'floorField' => 'born_date_floor_year',
                    'ceilingField' => 'death_date_ceiling_year',
                    'type' => $filters['date_search_type'],
                ];
                if (array_key_exists('from', $value)) {
                    $date_result['startDate'] = $value['from'];
                }
                if (array_key_exists('to', $value)) {
                    $date_result['endDate'] = $value['to'];
                }
                $result['date_range'][] = $date_result;
                break;
            case 'role':
            case 'self_designation':
            case 'office':
            case 'origin':
                $result['nested'][$key] = $value;
                break;
            case 'management':
                if (isset($filters['management_inverse']) && $filters['management_inverse']) {
                    $result['nested_toggle'][$key] = [$value, false];
                } else {
                    $result['nested_toggle'][$key] = [$value, true];
                }
                break;
            case 'name':
            case 'public_comment':
                $result['text'][$key] = [
                    'text' => $value,
                    'combination' => 'any',
                ];
                break;
            case 'comment':
                $result['multiple_text'][$key] = [
                    'public_comment'=> [
                        'text' => $value,
                        'combination' => 'any',
                    ],
                    'private_comment'=> [
                        'text' => $value,
                        'combination' => 'any',
                    ],
                ];
                break;
            case 'public':
            case 'historical':
            case 'modern':
                $result['boolean'][$key] = ($value === '1');
                break;
            }
        }
        return $result;
    }
}
