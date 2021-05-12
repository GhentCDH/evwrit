<?php

namespace App\Service\ElasticSearchService;

/**
 */
class Analysis
{
    /**
     * Elasticsearch config for Special Analysis
     * @var array
     */
    const ANALYSIS = [
        'filter' => [
        ],
        'char_filter' => [
            # Add 10 leading zeros
            'add_leading_zeros' => [
                'type' => 'pattern_replace',
                'pattern' => '(\\d+)',
                'replacement' => '0000000000$1',
            ],
            # Remove leading zeros so the total number of digits is 10
            'remove_leading_zeros' => [
                'type' => 'pattern_replace',
                'pattern' => '(0+)(?=\\d{10})',
                'replacement' => '',
            ],
            'remove_par_brackets_filter' => [
                'type' => 'mapping',
                'mappings' => [
                    '( =>',
                    ') =>',
                    '[ =>',
                    '] =>',
                    '< =>',
                    '> =>',
                    '| =>',
                    '+ =>',
                ],
            ],
            'remove_quotes_filter' => [
                'type' => 'mapping',
                'mappings' => [
                    '" =>',
                    '“ =>',
                    '” =>',
                    '« =>',
                    '» =>',
                    '\' =>',
                ],
            ],
        ],
        'normalizer' => [
            'case_insensitive' => [
                'filter' => [
                    'lowercase',
                ],
            ],
            'text_digits' => [
                'char_filter' => [
                    'add_leading_zeros',
                    'remove_leading_zeros',
                ],
            ],
        ],
    ];
}
