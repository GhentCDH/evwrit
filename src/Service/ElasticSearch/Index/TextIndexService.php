<?php

namespace App\Service\ElasticSearch\Index;

use App\Service\ElasticSearch\Analysis;
use App\Service\ElasticSearch\Client;

class TextIndexService extends AbstractIndexService
{
    const indexName = "texts";

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
            'text' => [
                'type' => 'text',
                'analyzer' => 'custom_greek_original',
            ],
            'text_lemmas' => [
                'type' => 'text',
                'analyzer' => 'custom_greek_original',
            ],
//            'keyword' => ['type' => 'nested'],
            'year_begin' => ['type' => 'short'],
            'year_end' => ['type' => 'short'],

            'agentive_role' => ['type' => 'nested'],
            'communicative_goal'  => ['type' => 'nested'],

            // ancient person
            'attestations' => [ 'type' => 'nested' ],


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

            'ancient_person' => ['type' => 'nested'],

            'level_category' => [ 'type' => 'nested' ],

            'annotations' =>  [ 'type' => 'nested' ],

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
                    ]
                ]
            ]
        ];
    }
}