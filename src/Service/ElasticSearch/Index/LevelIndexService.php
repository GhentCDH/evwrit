<?php

namespace App\Service\ElasticSearch\Index;

use App\Service\ElasticSearch\Analysis;
use App\Service\ElasticSearch\Client;

class LevelIndexService extends AbstractIndexService
{
    protected const indexName = "level";
    const INNER_HITS_SIZE_MAX = 100;

    public function __construct(Client $client)
    {
        parent::__construct(
            $client,
            self::indexName);
    }

    protected function getMappingProperties(): array {
        return [
            // physical info
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

            // com info
//            'archive' => ['type' => 'object'],
//            'text_type' => ['type' => 'object'],
//            'text_subtype' => ['type' => 'object'],
//            'social_distance' => ['type' => 'object'],
            'agentive_role' => ['type' => 'nested'],
            'communicative_goal'  => ['type' => 'nested'],

            // ancient person
            'ancient_person' => [ 'type' => 'nested' ],

//            'production_stage' => ['type' => 'nested'],
//            'text_format' => ['type' => 'nested'],
//            'writing_direction' => ['type' => 'nested'],

            'columns.min' => ['type' => 'short'],
            'columns.max' => ['type' => 'short'],
            'letters_per_line.min' => ['type' => 'short'],
            'letters_per_line.max' => ['type' => 'short'],

            'lines.min' => ['type' => 'short'],
            'lines.max' => ['type' => 'short'],

            'margin_left' => ['type' => 'half_float'],
            'margin_right' => ['type' => 'half_float'],
            'margin_top' => ['type' => 'half_float'],
            'margin_bottom' => ['type' => 'half_float'],
            'interlinear_space' => ['type' => 'half_float'],
            'width' => ['type' => 'half_float'],
            'height' => ['type' => 'half_float'],

            'is_recto' => ['type' => 'boolean'],
            'is_verso' => ['type' => 'boolean'],
            'is_transversa_charta' => ['type' => 'boolean'],

            'annotations' => ['type' => 'nested'],

            // level
            'level_category' => [ 'type' => 'nested' ],
//            'physical_objects' => ['type' => 'nested'],
//            'greek_latins' => ['type' => 'nested'],

            'attestations' => [
                'type' => 'nested',
            ],
        ];
    }

    protected function getIndexProperties(): array {
        return [
            'settings' => [
                'analysis' => Analysis::ANALYSIS,
                'index' => [
                    'mapping' => [
                        'total_fields' => [
                            'limit' => 2000
                        ]
                    ],
                    'max_inner_result_window' => self::INNER_HITS_SIZE_MAX
                ]
            ]
        ];
    }
}