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
            'ancient_person' => [ 'type' => 'nested' ],

            'production_stage' => ['type' => 'nested'],
            'text_format' => ['type' => 'nested'],
            'writing_direction' => ['type' => 'nested'],

            'columns_min' => ['type' => 'short'],
            'columns_max' => ['type' => 'short'],
            'letters_per_line_min' => ['type' => 'short'],
            'letters_per_line_max' => ['type' => 'short'],

            'lines_min' => ['type' => 'short'],
            'lines_max' => ['type' => 'short'],

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

            'annotations' => [
                'properties' => [
                    'language' => ['type' => 'nested'],
                    'typography' => ['type' => 'nested'],
                    'lexis' => ['type' => 'nested'],
                    'orthography' => ['type' => 'nested'],
                    'morphology' => ['type' => 'nested'],
                ],
            ],
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