<?php

namespace App\Service\ElasticSearch;

class Configs implements SearchConfigInterface
{
    const ignoreUnknownUncertain = ['unknown','uncertain', 'Unknown', 'Uncertain', 'Unknwon'];

    public static function filterPhysicalInfo(): array
    {
        return [
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
            'script' => ['type' => self::FILTER_OBJECT_ID],
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
    }

    public static function aggregatePhysicalInfo(): array
    {
        return [
            'era' => ['type' => self::AGG_OBJECT_ID_NAME],
            'keyword' => ['type' => self::AGG_OBJECT_ID_NAME],
            'language' => ['type' => self::AGG_OBJECT_ID_NAME],
            'script' => ['type' => self::AGG_OBJECT_ID_NAME],
            'location_written' => ['type' => self::AGG_OBJECT_ID_NAME],
            'location_found' => ['type' => self::AGG_OBJECT_ID_NAME],
            'form'  => ['type' => self::AGG_OBJECT_ID_NAME],
        ];
    }

    public static function filterAdministrative(): array
    {
        return [
            'project' => ['type' => self::FILTER_OBJECT_ID],
            'collaborator' => ['type' => self::FILTER_OBJECT_ID],
        ];
    }

    public static function aggregateAdministrative(): array
    {
        return [
            'collaborator'  => ['type' => self::AGG_OBJECT_ID_NAME],
            'project'  => [
                'type' => self::AGG_OBJECT_ID_NAME,
                'limitId' => [2,3,4,9] //todo: fix this!!
            ],
        ];
    }

    public static function filterCommunicativeInfo(): array
    {
        return [
            'archive' => ['type' => self::FILTER_OBJECT_ID],
            'social_distance' => ['type' => self::FILTER_OBJECT_ID],
            'text_type' => ['type' => self::FILTER_OBJECT_ID],
            'text_subtype' => ['type' => self::FILTER_OBJECT_ID],
            'agentive_role' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'agentive_role'
            ],
            'communicative_goal' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'communicative_goal'
            ],
            'generic_agentive_role' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'agentive_role'
            ],
            'generic_communicative_goal' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'communicative_goal'
            ],
        ];
    }

    public static function aggregateCommunicativeInfo(): array
    {
        return [
            'archive' => ['type' => self::AGG_OBJECT_ID_NAME],
            'social_distance' => [
                'type' => self::AGG_OBJECT_ID_NAME,
                'ignoreValue' => self::ignoreUnknownUncertain,
            ],
            'text_type' => ['type' => self::AGG_OBJECT_ID_NAME],
            'text_subtype' => ['type' => self::AGG_OBJECT_ID_NAME],
            'agentive_role' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'agentive_role',
                'filter' => [ 'generic_agentive_role' => 'generic_agentive_role.id' ]
            ],
            'generic_agentive_role' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'agentive_role',
            ],
            /* goal */
            'communicative_goal' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'communicative_goal',
                'filter' => [ 'generic_communicative_goal' => 'generic_communicative_goal.id' ]
            ],
            'generic_communicative_goal' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'communicative_goal',
            ],
        ];
    }

    public static function filterMateriality(): array
    {
        return [
            'production_stage' => ['type' => self::FILTER_OBJECT_ID],
            'material' => ['type' => self::FILTER_OBJECT_ID],
            'text_format' => ['type' => self::FILTER_OBJECT_ID],
            'writing_direction' => ['type' => self::FILTER_OBJECT_ID],

            'is_recto' => ['type' => self::FILTER_BOOLEAN],
            'is_verso' => ['type' => self::FILTER_BOOLEAN],
            'is_transversa_charta' => ['type' => self::FILTER_BOOLEAN],

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
        ];
    }

    public static function aggregateMateriality(): array
    {
        return [
            'production_stage' => ['type' => self::AGG_OBJECT_ID_NAME],
            'material'  => ['type' => self::AGG_OBJECT_ID_NAME],
            'text_format' => ['type' => self::AGG_OBJECT_ID_NAME],
            'writing_direction' => ['type' => self::AGG_OBJECT_ID_NAME],

            'is_recto' => ['type' => self::AGG_BOOLEAN],
            'is_verso' => ['type' => self::AGG_BOOLEAN],
            'is_transversa_charta' => ['type' => self::AGG_BOOLEAN],

            'lines_max' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'lines.max'],
            'lines_min' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'lines.min'],
            'columns_min' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'columns_min'],
            'columns_max' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'columns_max'],
            'letters_per_line_min' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'letters_per_line.min'],
            'letters_per_line_max' => ['type' => self::AGG_GLOBAL_STATS, 'field' => 'letters_per_line.max'],
            'width' => ['type' => self::AGG_GLOBAL_STATS],
            'height' => ['type' => self::AGG_GLOBAL_STATS],
        ];
    }

    public static function filterAncientPerson(): array
    {
        return [
            'ap_tm_id' => [
                'type' => self::FILTER_NUMERIC,
                'nested_path' => 'ancient_person',
                'field' => 'tm_id'
            ],
            'ap_role' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'ancient_person',
                'field' => 'role'
            ],
            'ap_gender' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'ancient_person',
                'field' => 'gender'
            ],
            'ap_occupation' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'ancient_person',
                'field' => 'occupation'
            ],
            'ap_social_rank' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'ancient_person',
                'field' => 'social_rank'
            ],
            'ap_honorific_epithet' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'ancient_person',
                'field' => 'honorific_epithet'
            ],
            'ap_graph_type' => [
                'type' => self::FILTER_NESTED_ID,
                'nested_path' => 'ancient_person',
                'field' => 'graph_type'
            ],
        ];
    }

    public static function aggregateAncientPerson(): array
    {
        return [
            'ap_name' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => ''
            ],
            'ap_role' => [
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => 'role',
                'ignoreValue' => self::ignoreUnknownUncertain,
            ],
            'ap_gender' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => 'gender'
            ],
            'ap_occupation' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => 'occupation'
            ],
            'ap_social_rank' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => 'social_rank'
            ],
            'ap_honorific_epithet' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => 'honorific_epithet'
            ],
            'ap_graph_type' => [
                'ignoreValue' => self::ignoreUnknownUncertain,
                'type' => self::AGG_NESTED_ID_NAME,
                'nested_path' => 'ancient_person',
                'field' => 'graph_type'
            ],
        ];
    }

}