<?php

namespace App\Service\ElasticSearch;

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
            'year_begin' => ['type' => 'short'],
            'year_end' => ['type' => 'short'],
            'archive' => ['type' => 'object'],
            'era' => ['type' => 'object'],
            'keyword' => ['type' => 'nested'],
            'language' => ['type' => 'nested'],
            'script' => ['type' => 'nested'],
            'material' => ['type' => 'nested'],
            'project' => ['type' => 'nested'],
            'social_distance' => ['type' => 'nested'],
            'text_type' => ['type' => 'object'],
            'text_subtype' => ['type' => 'object'],
            'location_found' => ['type' => 'nested'],
            'location_written' => ['type' => 'nested'],
            'agentive_role' => ['type' => 'nested'],
            'communicative_goal'  => ['type' => 'nested'],
            'ancient_person' => [ 'type' => 'nested' ],

            'production_stage' => ['type' => 'nested'],
            'text_format' => ['type' => 'nested'],
            'writing_direction' => ['type' => 'nested'],

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

            'annotations' => ['type' => 'nested']
        ];
    }

    protected function getIndexProperties(): array {
        return [
            'settings' => [
                'analysis' => Analysis::ANALYSIS
            ]
        ];
    }
}