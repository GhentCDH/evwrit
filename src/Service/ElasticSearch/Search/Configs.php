<?php

namespace App\Service\ElasticSearch\Search;

use App\Service\ElasticSearch\Index\LevelIndexService;
use App\Service\ElasticSearch\Index\TextIndexService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Configs implements SearchConfigInterface
{
    const ignoreUnknownUncertain = ['unknown','uncertain', 'Unknown', 'Uncertain', 'Unknwon'];

    private array $allowedProjectIds = [];
    private ?int $defaultProjectId = null;
    private ?ContainerInterface $container = null;

    private array $baseAnnotationTypes = [
        'typography', 'lexis', 'orthography', 'morphology', 'language', 'morpho_syntactical'
    ];

    private array $baseAnnotationProperties = [
            'typography' => ['wordSplitting','correction','insertion', 'abbreviation', 'deletion', 'symbol', 'punctuation', 'accentuation', 'vacat', 'accronym', 'positionInText', 'wordClass'],
            'lexis' => ['standardForm','type','subtype','wordclass','formulaicity','prescription','proscription','identifier'],
            'orthography' => ['standardForm','type','subtype','wordclass','formulaicity','positionInWord'],
            'morphology' => ['standardForm','type','subtype','wordclass','formulaicity','positionInWord'],
            'language' => ['bigraphismDomain', 'bigraphismRank', 'bigraphismFormulaicity', 'bigraphismType', 'codeswitchingType', 'codeswitchingRank', 'codeswitchingDomain', 'codeswitchingFormulaicity' ],
            'morpho_syntactical' => [
                'coherenceForm', 'coherenceContent', 'coherenceContext',
                'complementationForm', 'complementationContent', 'complementationContext',
                'subordinationForm', 'subordinationContent', 'subordinationContext',
                'relativisationForm', 'relativisationContent', 'relativisationContext',
                'typeFormulaicity'
            ],
            'handshift' => ['abbreviation','accentuation','connectivity','correction','curvature','degreeOfFormality','expansion','lineation','orientation','punctuation','regularity','scriptType','slope','wordSplitting', 'status'],
            'gts' => ['part'],
            'gtsa' => ['type', 'subtype', 'speechAct'],
        ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        if ($container->hasParameter('app.allowed_project_ids')) {
            $this->allowedProjectIds = array_map(fn($i) => (int) $i, (array) $container->getParameter('app.allowed_project_ids'));
        }
        if ($container->hasParameter('app.default_project_id')) {
            $this->defaultProjectId = (int) $container->getParameter('app.default_project_id');
        }
    }

    public function filterDefaults(): array
    {
        return [
            'boost' => 0
        ];
    }

    private function mergeFilterDefaults($filters): array
    {
        $defaults = self::filterDefaults();
        foreach($filters as $index => $config) {
            $filters[$index] = array_merge($defaults, $config);
        }

        return $filters;
    }

    public function filterPhysicalInfo(): array
    {
        $filters = [
            'id' => ['type' => self::FILTER_NUMERIC],
            'tm_id' => ['type' => self::FILTER_NUMERIC],
            'title' => [
//                'type' => self::FILTER_KEYWORD,
//                'field' => 'title.keyword'
                'type' => self::FILTER_TEXT,
            ],
            'text' => ['type' => self::FILTER_TEXT],
            'text_lemmas' => ['type' => self::FILTER_TEXT],
            'date'=> [
                'type' => self::FILTER_DATE_RANGE,
                'floorField' => 'year_begin',
                'ceilingField' => 'year_end',
                'typeField' => 'date_search_type',
            ],
            'era' => ['type' => self::FILTER_OBJECT_ID],
            'keyword' => ['type' => self::FILTER_OBJECT_ID],
            'language' => ['type' => self::FILTER_OBJECT_ID],
            'language_count' => [
                'type' => self::FILTER_NUMERIC,
            ],
            'script' => ['type' => self::FILTER_OBJECT_ID],
            'script_count' => ['type' => self::FILTER_NUMERIC],
            'location_written' => ['type' => self::FILTER_OBJECT_ID],
            'location_found' => ['type' => self::FILTER_OBJECT_ID],
            'form' => ['type' => self::FILTER_OBJECT_ID],
            'has_translation' => [
                'type' => self::FILTER_EXISTS,
                'field' => 'translation',
            ],
            'has_image' => [
                'type' => self::FILTER_EXISTS,
                'field' => 'image',
            ],
        ];

        return $this->mergeFilterDefaults($filters);
    }

    public function aggregatePhysicalInfo(): array
    {
        return [
            'era' => ['type' => self::AGG_OBJECT_ID_NAME],
            'keyword' => ['type' => self::AGG_OBJECT_ID_NAME],
            'language' => ['type' => self::AGG_OBJECT_ID_NAME],
            'language_count' => [
                'type' => self::AGG_NUMERIC,
                'mapLabel' => [
                    '0' => 'no languages specified',
                    '1' => 'monolingual',
                    '2' => 'bilingual',
                    '3' => 'trilingual'
                ]
            ],
            'script' => ['type' => self::AGG_OBJECT_ID_NAME],
            'script_count' => [
                'type' => self::AGG_NUMERIC,
                'mapLabel' => [
                    '0' => 'no scripts specified',
                    '1' => 'monoscriptal',
                    '2' => 'biscriptal',
                    '3' => 'triscriptal'
                ]
            ],
            'location_written' => ['type' => self::AGG_OBJECT_ID_NAME],
            'location_found' => ['type' => self::AGG_OBJECT_ID_NAME],
            'form'  => ['type' => self::AGG_OBJECT_ID_NAME],
        ];
    }

    public function filterCharacterRecognitionTool(): array
    {
        $filters = [
            'arabic_relative' => ['type' => self::FILTER_NUMERIC_RANGE_SLIDER, 'ignore' => [-1, 10000]],
            'greek_relative' => ['type' => self::FILTER_NUMERIC_RANGE_SLIDER, 'ignore' => [-1, 10000]],
            'latin_relative' => ['type' => self::FILTER_NUMERIC_RANGE_SLIDER, 'ignore' => [-1, 10000]],
            'coptic_relative' => ['type' => self::FILTER_NUMERIC_RANGE_SLIDER, 'ignore' => [-1, 10000]],
        ];
        return $this->mergeFilterDefaults($filters);
    }

    public function filterAdministrative(): array
    {
        $filters = [
            'project' => [
                'type' => self::FILTER_OBJECT_ID,
                'defaultValue' => $this->defaultProjectId !== null ? [$this->defaultProjectId] : null,
            ],
            'project_extra' => [
                'type' => self::FILTER_OBJECT_ID,
                'field' => 'project',
            ],
            'collaborator' => ['type' => self::FILTER_OBJECT_ID],
        ];
        return $this->mergeFilterDefaults($filters);
    }

    public function aggregateAdministrative(): array
    {
        $config = [
            'collaborator'  => ['type' => self::AGG_OBJECT_ID_NAME],
            'project'  => [
                'type' => self::AGG_OBJECT_ID_NAME,
//                'allowedValue' => [3, 2, 4, 9, 30, 35] //todo: fix this!!
                'allowedValue' => [...$this->allowedProjectIds, self::ANY_KEY],
                'defaultValue' => $this->defaultProjectId !== null ? [$this->defaultProjectId] : null,
            ],
            'project_extra'  => [
                'field' => 'project',
                'type' => self::AGG_OBJECT_ID_NAME,
//                'allowedValue' => [3, 2, 4, 9, 30, 35] //todo: fix this!!
                'allowedValue' => [...$this->allowedProjectIds, self::ANY_KEY],
            ],

        ];

        return $config;
    }

    public function filterCommunicativeInfo(): array
    {
        $filters = [
            'archive' => ['type' => self::FILTER_OBJECT_ID],
            'social_distance' => ['type' => self::FILTER_OBJECT_ID],
            'level_category_group' => [
                'type' => self::FILTER_NESTED_MULTIPLE,
                'nested_path' => 'level_category',
                'filters' => [
                    'level_category_category' => [
                        'type' => self::FILTER_OBJECT_ID
                    ],
                    'level_category_subcategory' => [
                        'type' => self::FILTER_OBJECT_ID
                    ],
                ]
            ],


            'agentive_role_group' => [
                'type' => self::FILTER_NESTED_MULTIPLE,
                'nested_path' => 'agentive_role',
                'filters' => [
                    'agentive_role' => [
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                    'generic_agentive_role' => [
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                ]
            ],
            'communicative_goal_group' => [
                'type' => self::FILTER_NESTED_MULTIPLE,
                'nested_path' => 'communicative_goal',
                'filters' => [
                    'communicative_goal_type' => [
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                    'communicative_goal_subtype' => [
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                ]
            ],

            'greek_latin_group' => [
                'type' => self::FILTER_NESTED_MULTIPLE,
                'nested_path' => 'greek_latin',
                'filters' => [
                    'greek_latin_label' => [
                        'type' => self::FILTER_KEYWORD,
                        'field' => 'label',
                    ],
                    'greek_latin_sublabel' => [
                        'type' => self::FILTER_KEYWORD,
                        'field' => 'sublabel',
                    ],
                    'greek_latin_english' => [
                        'type' => self::FILTER_KEYWORD,
                        'field' => 'english',
                    ],
                ]
            ],
        ];
        return $this->mergeFilterDefaults($filters);
    }

    public function aggregateCommunicativeInfo(): array
    {
        return [
            'archive' => ['type' => self::AGG_OBJECT_ID_NAME],
            'social_distance' => [
                'type' => self::AGG_OBJECT_ID_NAME,
                'ignoreValue' => self::ignoreUnknownUncertain,
            ],
            'level_category_category' => [
                'nested_path' => 'level_category',
                'type' => self::AGG_NESTED_ID_NAME,
//                'excludeFilter' => ['level_category_group'], // exclude filter of same type
            ],
            'level_category_subcategory' => [
                'nested_path' => 'level_category',
                'type' => self::AGG_NESTED_ID_NAME,
//                'excludeFilter' => ['level_category_group'], // exclude filter of same type
//                'filters' => self::filterCommunicativeInfo()['level_category_group']['filters']
            ],
            /* agentive role */
            'generic_agentive_role' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'agentive_role',
//                'excludeFilter' => ['agentive_role_group'], // exclude filter of same type
            ],
            'agentive_role' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'agentive_role',
//                'excludeFilter' => ['agentive_role_group'], // exclude filter of same type
//                'filters' => self::filterCommunicativeInfo()['agentive_role_group']['filters']
            ],
            /* communicative goal */
            'communicative_goal_type' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
//                'excludeFilter' => ['communicative_goal_group'], // exclude filter of same type
                'nested_path' => 'communicative_goal',
            ],
            'communicative_goal_subtype' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'communicative_goal',
//                'excludeFilter' => ['communicative_goal_group'], // exclude filter of same type
//                'filters' => self::filterCommunicativeInfo()['communicative_goal_group']['filters']
            ],
            'greek_latin_label' => [
                'type' => self::AGG_KEYWORD,
                'nested_path' => 'greek_latin',
                'field' => 'label'
            ],
            'greek_latin_sublabel' => [
                'type' => self::AGG_KEYWORD,
                'nested_path' => 'greek_latin',
                'field' => 'sublabel'
            ],
            'greek_latin_english' => [
                'type' => self::AGG_KEYWORD,
                'nested_path' => 'greek_latin',
                'field' => 'english'
            ],

        ];
    }

    public function filterMateriality(): array
    {
        $filters = [
            'production_stage' => ['type' => self::FILTER_OBJECT_ID],
            'material' => ['type' => self::FILTER_OBJECT_ID],
            'text_format' => ['type' => self::FILTER_OBJECT_ID],
            'writing_direction' => ['type' => self::FILTER_OBJECT_ID],

            'is_recto' => ['type' => self::FILTER_BOOLEAN],
            'is_verso' => ['type' => self::FILTER_BOOLEAN],
            'is_transversa_charta' => ['type' => self::FILTER_BOOLEAN],
            'tomos_synkollesimos' => ['type' => self::FILTER_BOOLEAN],
            'preservation_status_h' => ['type' => self::FILTER_OBJECT_ID],
            'preservation_status_w' => ['type' => self::FILTER_OBJECT_ID],

            'kollesis' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'ignore' => [-1, 10000]
            ],

            'lines' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'floorField' => 'lines.min',
                'ceilingField' => 'lines.max',
                'ignore' => [-1, 10000]
            ],
            'columns' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'floorField' => 'columns.min',
                'ceilingField' => 'columns.max',
                'ignore' => [-1, 10000]
            ],
            'letters_per_line' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'floorField' => 'letters_per_line.min',
                'ceilingField' => 'letters_per_line.max',
                'ignore' => [-1, 10000]
            ],
            'width' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'ignore' => [-1, 10000]
            ],
            'height' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'ignore' => [-1, 10000]
            ],
            'interlinear_space' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'ignore' => [-1, 10000]
            ],
            'margin_left' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'ignore' => [-1, 10000]
            ],
            'margin_right' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'ignore' => [-1, 10000]
            ],
            'margin_top' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'ignore' => [-1, 10000]
            ],
            'margin_bottom' => [
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'ignore' => [-1, 10000]
            ],
            'orientation' => [
                'field' => 'image.orientation',
                'type' => self::FILTER_KEYWORD,
            ],
            'kollemata' => [
                'field' => 'image.kollemata',
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'ignore' => [-1, 10000]
            ],
            'line_height' => [
                'field' => 'image.line_height',
                'type' => self::FILTER_NUMERIC_RANGE_SLIDER,
                'ignore' => [-1, 10000]
            ],

            'drawing' => ['type' => self::FILTER_OBJECT_ID],
            'margin_writing' => ['type' => self::FILTER_OBJECT_ID],
            'margin_filler' => ['type' => self::FILTER_OBJECT_ID],
        ];
        return $this->mergeFilterDefaults($filters);
    }

    public function aggregateMateriality(): array
    {
        return [
            'production_stage' => ['type' => self::AGG_OBJECT_ID_NAME],
            'material'  => ['type' => self::AGG_OBJECT_ID_NAME],
            'text_format' => ['type' => self::AGG_OBJECT_ID_NAME],
            'writing_direction' => ['type' => self::AGG_OBJECT_ID_NAME],
            'kollesis' => ['type' => self::AGG_GLOBAL_STATS],

            'preservation_status_h' => ['type' => self::AGG_OBJECT_ID_NAME],
            'preservation_status_w' => ['type' => self::AGG_OBJECT_ID_NAME],

            'is_recto' => ['type' => self::AGG_BOOLEAN],
            'is_verso' => ['type' => self::AGG_BOOLEAN],
            'is_transversa_charta' => ['type' => self::AGG_BOOLEAN],
            'tomos_synkollesimos' => ['type' => self::AGG_BOOLEAN],

            'orientation' => [
                'field' => 'image.orientation',
                'type' => self::AGG_KEYWORD,
            ],
            'kollemata_count' => [
                'field' => 'image.kollemata_count',
                'type' => self::AGG_NUMERIC,
            ],

            'lines_max' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'lines.max'],
            'lines_min' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'lines.min'],
            'columns_min' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'columns_min'],
            'columns_max' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'columns_max'],
            'letters_per_line_min' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'letters_per_line.min'],
            'letters_per_line_max' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'letters_per_line.max'],
            'width' => ['type' => self::AGG_GLOBAL_STATS],
            'height' => ['type' => self::AGG_GLOBAL_STATS],
            'interlinear_space' => ['type' => self::AGG_GLOBAL_STATS],
            'line_height' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'image.line_height'],
            'margin_left' => ['type' => self::AGG_GLOBAL_STATS],
            'margin_right' => ['type' => self::AGG_GLOBAL_STATS],
            'margin_top' => ['type' => self::AGG_GLOBAL_STATS],
            'margin_bottom' => ['type' => self::AGG_GLOBAL_STATS],
            'kollemata' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'image.kollemata'],

            'drawing'  => ['type' => self::AGG_OBJECT_ID_NAME],
            'margin_writing'  => ['type' => self::AGG_OBJECT_ID_NAME],
            'margin_filler'  => ['type' => self::AGG_OBJECT_ID_NAME],
        ];
    }

    public function filterAncientPerson(): array
    {
        $filters = [
            'ancient_person' => [
                'type' => self::FILTER_NESTED_MULTIPLE,
                'nested_path' => 'ancient_person',
                'filters' => [
                    'ap_name' => [
                        'field' => 'name',
                        'type' => self::FILTER_KEYWORD,
                    ],
                    'ap_tm_id' => [
                        'field' => 'tm_id',
                        'type' => self::FILTER_NUMERIC,
                    ],
                    'ap_role' => [
                        'field' => 'role',
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                    'ap_gender' => [
                        'field' => 'gender',
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                    'ap_occupation_en' => [
                        'field' => 'occupation.name.en',
                        'type' => self::FILTER_KEYWORD,
                    ],
                    'ap_occupation_gr' => [
                        'field' => 'occupation',
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                    'ap_social_rank' => [
                        'field' => 'social_rank',
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                    'ap_honorific_epithet' => [
                        'field' => 'honorific_epithet',
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                    'ap_graph_type' => [
                        'field' => 'graph_type',
                        'type' => self::FILTER_OBJECT_ID,
                    ],
// todo: add attestation level filter
//                    'ap_level' => [
//                        'field' => 'level.number',
//                        'type' => self::FILTER_NUMERIC,
//                        'param_name' => 'textLevel'
//                    ]
                ]
            ]
        ];
        return $this->mergeFilterDefaults($filters);
    }

    public function filterAttestations(): array
    {
        $filters = [
            'attestations' => [
                'type' => self::FILTER_NESTED_MULTIPLE,
                'nested_path' => 'attestations',
                'filters' => [
                    'ap_name' => [
                        'field' => 'attestations.name',
                        'type' => self::FILTER_KEYWORD,
                    ],
                    'ap_tm_id' => [
                        'field' => 'attestations.tm_id',
                        'type' => self::FILTER_NUMERIC,
                    ],
                    'ap_gender' => [
                        'field' => 'attestations.gender',
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                    'ap_graph_type' => [
                        'field' => 'attestations.graph_type',
                        'type' => self::FILTER_OBJECT_ID,
                    ],

                    'ap_role' => [
                        'field' => 'attestations.role',
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                    'ap_occupation_en' => [
                        'field' => 'attestations.occupation.name.en',
                        'type' => self::FILTER_KEYWORD,
                    ],
                    'ap_occupation_gr' => [
                        'field' => 'attestations.occupation',
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                    'ap_social_rank' => [
                        'field' => 'attestations.social_rank',
                        'type' => self::FILTER_OBJECT_ID,
                    ],
                    'ap_honorific_epithet' => [
                        'field' => 'attestations.honorific_epithet',
                        'type' => self::FILTER_OBJECT_ID,
                    ],
// todo: add attestation level filter
//                    'ap_level' => [
//                        'field' => 'level.number',
//                        'type' => self::FILTER_NUMERIC,
//                        'param_name' => 'textLevel'
//                    ]
                ]
            ]
        ];
        return $this->mergeFilterDefaults($filters);
    }

    public function aggregateAncientPerson(): array
    {
        return [
            'ap_name' => [
                'type' => self::AGG_KEYWORD, 
                'field' => 'name',
                'nested_path' => 'ancient_person',
                'excludeFilter' => ['ancient_person'],
                'filters' => $this->filterAncientPerson()['ancient_person']['filters'],
            ],
            'ap_tm_id' => [
                'type' => self::AGG_NUMERIC,
                'field' => 'tm_id',
                'nested_path' => 'ancient_person',
                'excludeFilter' => ['ancient_person'],
                'filters' => $this->filterAncientPerson()['ancient_person']['filters'],
            ],
            'ap_role' => [
                'type' => self::AGG_NESTED_ID_NAME, 
                'field' => 'role',
                'nested_path' => 'ancient_person',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['ancient_person'],
                'filters' => $this->filterAncientPerson()['ancient_person']['filters'],
            ],
            'ap_gender' => [
                'type' => self::AGG_NESTED_ID_NAME, 
                'field' => 'gender',
                'nested_path' => 'ancient_person',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['ancient_person'],
                'filters' => $this->filterAncientPerson()['ancient_person']['filters'],
            ],
            'ap_occupation_gr' => [
                'type' => self::AGG_NESTED_ID_NAME, 
                'field' => 'occupation',
                'locale' => 'gr',
                'nested_path' => 'ancient_person',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['ancient_person'],
                'filters' => $this->filterAncientPerson()['ancient_person']['filters'],
            ],
            'ap_occupation_en' => [
                'type' => self::AGG_KEYWORD,
                'field' => 'occupation.name.en',
                'nested_path' => 'ancient_person',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['ancient_person'],
                'filters' => $this->filterAncientPerson()['ancient_person']['filters'],
            ],
            'ap_social_rank' => [
                'type' => self::AGG_NESTED_ID_NAME, 
                'field' => 'social_rank',
                'nested_path' => 'ancient_person',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['ancient_person'],
                'filters' => $this->filterAncientPerson()['ancient_person']['filters'],
            ],
            'ap_honorific_epithet' => [
                'type' => self::AGG_NESTED_ID_NAME, 
                'field' => 'honorific_epithet',
                'nested_path' => 'ancient_person',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['ancient_person'],
                'filters' => $this->filterAncientPerson()['ancient_person']['filters'],
            ],
            'ap_graph_type' => [
                'type' => self::AGG_NESTED_ID_NAME, 
                'field' => 'graph_type',
                'nested_path' => 'ancient_person',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['ancient_person'],
                'filters' => $this->filterAncientPerson()['ancient_person']['filters'],
            ],
        ];
    }

    public function aggregateAttestations(): array
    {
        return [
            'ap_name' => [
                'type' => self::AGG_KEYWORD,
                'field' => 'attestations.name',
                'nested_path' => 'attestations',
                'excludeFilter' => ['attestations'],
                'filters' => $this->filterAttestations()['attestations']['filters'],
            ],
            'ap_tm_id' => [
                'type' => self::AGG_NUMERIC,
                'field' => 'attestations.tm_id',
                'nested_path' => 'attestations',
                'excludeFilter' => ['attestations'],
                'filters' => $this->filterAttestations()['attestations']['filters'],
            ],
            'ap_role' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'field' => 'attestations.role',
                'nested_path' => 'attestations',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['attestations'],
                'filters' => $this->filterAttestations()['attestations']['filters'],
            ],
            'ap_gender' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'field' => 'attestations.gender',
                'nested_path' => 'attestations',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['attestations'],
                'filters' => $this->filterAttestations()['attestations']['filters'],
            ],
            'ap_occupation_gr' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'field' => 'attestations.occupation',
                'locale' => 'gr',
                'nested_path' => 'attestations',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['attestations'],
                'filters' => $this->filterAttestations()['attestations']['filters'],
            ],
            'ap_occupation_en' => [
                'type' => self::AGG_KEYWORD,
                'field' => 'attestations.occupation.name.en',
                'nested_path' => 'attestations',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['attestations'],
                'filters' => $this->filterAttestations()['attestations']['filters'],
            ],
            'ap_social_rank' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'field' => 'attestations.social_rank',
                'nested_path' => 'attestations',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['attestations'],
                'filters' => $this->filterAttestations()['attestations']['filters'],
            ],
            'ap_honorific_epithet' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'field' => 'attestations.honorific_epithet',
                'nested_path' => 'attestations',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['attestations'],
                'filters' => $this->filterAttestations()['attestations']['filters'],
            ],
            'ap_graph_type' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'field' => 'attestations.graph_type',
                'nested_path' => 'attestations',
                'ignoreValue' => self::ignoreUnknownUncertain,
                'excludeFilter' => ['attestations'],
                'filters' => $this->filterAttestations()['attestations']['filters'],
            ],
        ];
    }

    public function filterBaseAnnotations(): array
    {
        $searchFilters = [];

        // build annotation filters
        // 1. add annotation type filter
        // 2. add property filters

        $searchFilters['annotations'] = [
            'type' => self::FILTER_NESTED_MULTIPLE,
            'nested_path' => 'annotations',
            'filters' => [
                'annotation_id' => [
                    'field' => 'id',
                    'type' => self::FILTER_NUMERIC,
                ],
                'annotation_text' => [
                    'field' => 'context.text',
                    'type' => self::FILTER_TEXT
                ],
                'annotation_type' => [
                    'field' => 'type',
                    'type' => self::FILTER_KEYWORD
                ],
                'gts_textLevel' => [
                    'field' => 'properties.gts_textLevel.number',
                    'type' => self::FILTER_NUMERIC
                ],
            ],
            'innerHits' => [
                'size' => TextIndexService::INNER_HITS_SIZE_MAX
            ],
            'scoreMode' => 'sum',
            'boost' => 1
        ];

        $annotationProperties = $this->baseAnnotationProperties;
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

    public function aggregateBaseAnnotations(array $allowedAnnotationTypes = []): array
    {
        $aggregationFilters = [];

        // remove aggregations not in scope
        $annotationProperties = $this->baseAnnotationProperties;
        if ( count($allowedAnnotationTypes) ) {
            foreach($this->baseAnnotationTypes as $annotationType) {
                if ( !in_array($annotationType, $allowedAnnotationTypes) ) {
                    unset($annotationProperties[$annotationType]);
                }
            }
        }

        // add annotation property filters
        foreach( $annotationProperties as $type => $properties ) {
            foreach( $properties as $property ) {
                $filter_name = "{$type}_{$property}";
                $field_name = "properties.{$type}_{$property}";
                $aggregationFilters[$filter_name] = [
                    'type' => self::AGG_NESTED_ID_NAME,
                    'field' => $field_name,
                    'nested_path' => "annotations",
                    'condition' => in_array($type, $this->baseAnnotationTypes) ?
                        $this->baseAnnotationAggregationIsActive($type) : null,
                ];
            }
        }

        // add annotation type filter
        $aggregationFilters['annotation_type'] = [
            'type' => self::AGG_KEYWORD,
            'field' => 'type',
            'nested_path' => "annotations",
            'replaceLabel' => [
                'search' => 'morpho_syntactical',
                'replace' => 'syntax'
            ]
        ];
        if (count($allowedAnnotationTypes)) {
            $aggregationFilters['annotation_type']['allowedValue'] = $allowedAnnotationTypes;
        }

        // add annotation type filter
        $aggregationFilters['gts_textLevel'] = [
            'type' => self::AGG_NUMERIC,
            'field' => 'properties.gts_textLevel.number',
            'nested_path' => "annotations",
        ];

        return $aggregationFilters;
    }

    public function filterTextStructure(): array
    {
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
                'size' => LevelIndexService::INNER_HITS_SIZE_MAX,
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

    protected function baseAnnotationAggregationIsActive(string $type): callable {
        return function($name, $config, $filterValues) use ($type) {
            return ( count($filterValues["annotation_type"]["value"] ?? []) === 1 && $filterValues["annotation_type"]["value"][0] === $type );
        };
    }
}