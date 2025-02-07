import Vue from 'vue'
import AbstractSearch from './AbstractSearch'
import VueFormGenerator from 'vue-form-generator'

import AbstractField from '../FormFields/AbstractField'
import CheckboxSwitch from '../FormFields/CheckboxSwitch'

import fieldRadio from '../FormFields/fieldRadio'

import SharedSearch from "./SharedSearch"

import Case from 'case'

Vue.component('fieldRadio', fieldRadio);

export default {
    data() {
        return {
        }
    },
    methods: {
        generalInformationFields(includeMaterial = false, expertOnly = false) {
            return {
                expertOnly: expertOnly,
                visible: this.fieldVisible,
                collapsible: true,
                collapsed: this.groupCollapsed,
                legend: 'General information',
                id: 'generalInformation',
                fields: [
                    {
                        type: 'input',
                        inputType: 'text',
                        label: 'Title',
                        model: 'title',
                    },
                    {
                        type: 'radio',
                        styleClasses: 'field-checkboxes-labels-only field-checkboxes-lg',
                        label: 'Word combination options:',
                        model: 'title_combination',
                        parentModel: 'title',
                        values: [
                            { value: 'any', name: 'any', toggleGroup: 'all_any_phrase' },
                            { value: 'all', name: 'all', toggleGroup: 'all_any_phrase' },
                            { value: 'phrase', name: 'consecutive words', toggleGroup: 'all_any_phrase' },
                        ],
                    },
                    {
                        type: 'input',
                        inputType: 'text',
                        label: 'Text ID',
                        model: 'id',
                    },
                    {
                        type: 'input',
                        inputType: 'text',
                        label: 'Trismegistos ID',
                        model: 'tm_id',
                    },
                    {
                        type: 'input',
                        inputType: 'number',
                        label: 'Year from',
                        model: 'year_begin',
                        min: AbstractSearch.YEAR_MIN,
                        max: AbstractSearch.YEAR_MAX,
                        validator: VueFormGenerator.validators.number,
                    },
                    {
                        type: 'input',
                        inputType: 'number',
                        label: 'Year to',
                        model: 'year_end',
                        min: AbstractSearch.YEAR_MIN,
                        max: AbstractSearch.YEAR_MAX,
                        validator: VueFormGenerator.validators.number,
                    },
                    {
                        type: 'radio',
                        label: 'The text date interval must ... the search date interval:',
                        labelClasses: 'control-label',
                        model: 'date_search_type',
                        hideActiveFilter: true,
                        values: [
                            {value: 'exact', name: 'exactly match'},
                            {value: 'included', name: 'be included in'},
                            {value: 'overlap', name: 'overlap with'},
                        ],
                    },
                    this.createMultiSelect('Era',
                        {
                            model: 'era'
                        },
                    ),
                    this.createMultiSelect('Location found',
                        {
                            model: 'location_found'
                        }
                    ),
                    this.createMultiSelect('Location written',
                        {
                            model: 'location_written',
                        }
                    ),
                    this.createOperators('language_op', {
                        collapsible: true,
                        collapsed: this.groupCollapsed,
                    }),
                    this.createMultiSelect('Language',
                        {
                            model: 'language'
                        },
                    ),
                    this.createMultiSelect('Language count',
                        {
                            model: 'language_count'
                        },
                    ),
                    this.createOperators('script_op', {
                        collapsible: true,
                        collapsed: this.groupCollapsed,
                    }),
                    this.createMultiSelect('Script',
                        {
                            model: 'script'
                        },
                    ),
                    this.createMultiSelect('Script count',
                        {
                            model: 'script_count'
                        },
                    ),
                    includeMaterial ? this.createMultiSelect('Material',
                        {
                            model: 'material',
                        },
                    ) : null,
                    this.createMultiSelect('Keyword',
                        {
                            model: 'keyword'
                        }
                    ),
                ].filter( item => item !== null )
            }
        },
        communicativeInformationFields(expertOnly = false) {
            return {
                collapsible: true,
                collapsed: this.groupCollapsed,
                legend: 'Communicative information',
                id: 'communicativeInformation',
                expertOnly: expertOnly,
                visible: this.fieldVisible,
                fields: [
                    this.createMultiSelect('Archive',
                        {
                            model: 'archive'
                        },
                    ),
                    this.createSelect('Text type', {model: 'level_category_category'}),
                    this.createSelect('Text subtype', {
                        model: 'level_category_subcategory',
                        'dependency': 'level_category_category'
                    }),
                    this.createSelect('Ancient category', {model: 'greek_latin_label'}),
                    this.createMultiSelect('Social distance',
                        {
                            model: 'social_distance'
                        },
                    ),
                    this.createSelect('Generic agentive role', {model: 'generic_agentive_role'}),
                    this.createSelect('Agentive role', {model: 'agentive_role', 'dependency': 'generic_agentive_role'}),
                    this.createSelect('Communicative goal type', {model: 'communicative_goal_type'}),
                    this.createSelect('Communicative goal subtype', {
                        model: 'communicative_goal_subtype',
                        'dependency': 'communicative_goal_type'
                    }),
                ]
            }
        },
        ancientPersonFields(expertOnly = false) {
            return {
                expertOnly: expertOnly,
                visible: this.fieldVisible,
                collapsible: true,
                collapsed: this.groupCollapsed,
                legend: 'Ancient persons',
                id: 'ancientPersons',
                fields: [
                    this.createMultiSelect('Name', {model: 'ap_name'}),
                    {
                        type: 'input',
                        inputType: 'text',
                        label: 'Trismegistos ID',
                        model: 'ap_tm_id',
                    },
                    this.createMultiSelect('Role', {model: 'ap_role'}),
                    this.createMultiSelect('Gender', {model: 'ap_gender'}),
                    this.createMultiSelect('Occupation', {model: 'ap_occupation_gr'}),
                    this.createMultiSelect('Occupation (English) ', {model: 'ap_occupation_en'}),
                    this.createMultiSelect('Social rank', {model: 'ap_social_rank'}),
                    this.createMultiSelect('Honorific epithet', {model: 'ap_honorific_epithet'}),
                    this.createMultiSelect('Type graph', {model: 'ap_graph_type'}),
                ]
            }
        },
        administrativeInformationFields(expertOnly = true) {
            return {
                expertOnly: expertOnly,
                visible: this.fieldVisible,
                collapsible: true,
                collapsed: this.groupCollapsed,
                id: 'administrativeInformation',
                legend: 'Administrative information',
                fields: [
                    this.createOperators('project_op', {
                        collapsible: true,
                        collapsed: this.groupCollapsed,
                    }),
                    this.createMultiSelect('Project',
                        {
                            model: 'project',
                        },
                    ),

                    this.createOperators('project_extra_op', {
                        collapsible: true,
                        collapsed: this.groupCollapsed,
                    }),
                    this.createMultiSelect('Project (additional filter)',
                        {
                            model: 'project_extra',
                        },
                    ),

                    this.createMultiSelect('Collaborator', {model: 'collaborator'}),
                ]
            }
        },
        materialityFields(expertOnly = false) {
            return {
                expertOnly: expertOnly,
                visible: this.fieldVisible,
                collapsible: true,
                collapsed: this.groupCollapsed,
                legend: 'Materiality',
                id: 'materiality',
                fields: [
                    this.createMultiSelect('Production stage',
                        {
                            model: 'production_stage'
                        }
                    ),
                    this.createMultiSelect('Material',
                        {
                            model: 'material'
                        }
                    ),
                    this.createMultiSelect('Format',
                        {
                            model: 'text_format'
                        }
                    ),
                    this.createMultiSelect('Orientation',
                        {
                            model: 'orientation'
                        }
                    ),
                    this.createMultiSelect('Writing direction',
                        {
                            model: 'writing_direction'
                        }
                    ),

                    this.createMultiSelect('Recto', {model: 'is_recto'}),
                    this.createMultiSelect('Verso', {model: 'is_verso'}),
                    this.createMultiSelect('Transversa charta', {model: 'is_transversa_charta'}),
                    this.createMultiSelect('Tomos Synkollesimos', {model: 'tomos_synkollesimos'}),

                    this.createMultiSelect('Preservation status Width', {model: 'preservation_status_w'}),
                    this.createMultiSelect('Preservation status Height', {model: 'preservation_status_h'}),

                    this.createMultiSelect('Number of Kollemata', {model: 'kollemata_count'}),
                    this.createRangeSlider('kollemata', 'Kollemata', 0, 50, 0.1),

                    this.createRangeSlider('kollesis', 'Kollesis', 0, 50, 1),
                    this.createRangeSlider('lines', 'Text lines', 0, 160, 1),
                    this.createRangeSlider('columns', 'Text columns', 0, 10, 1),
                    this.createRangeSlider('letters_per_line', 'Letters per line', 0, 220, 1),
                    this.createRangeSlider('width', 'Width', 0, 320, 1),
                    this.createRangeSlider('height', 'Height', 0, 300, 1),

                    this.createRangeSlider('interlinear_space','Interlinear space',0,22,0),
                    this.createRangeSlider('line_height','Line height',0,5,0.1, 1),

                    this.createRangeSlider('margin_left','Margin Left',0,410,1),
                    this.createRangeSlider('margin_right','Margin Right',0,410,1),
                    this.createRangeSlider('margin_top','Margin Top',0,350,1),
                    this.createRangeSlider('margin_bottom','Margin Bottom',0,1050,1),

                    // global typography fields
                    this.createMultiSelect('Drawings', {
                        model: 'drawing',
                    }),
                    this.createMultiSelect('Writings in the margins', {
                        model: 'margin_writing',
                    }),
                    this.createMultiSelect('Margin fillers', {
                        model: 'margin_filler',
                    }),

                ]
            }
        },
        annotationsFields(expertOnly = false, defaultAnnotationType = null) {
            // console.log(defaultAnnotationType)

            return {
                expertOnly: expertOnly,
                visible: this.fieldVisible,
                legend: 'Annotations',
                collapsible: true,
                collapsed: this.groupCollapsed,
                id: 'annotations',
                fields: [
                    {
                        type: 'input',
                        inputType: 'text',
                        label: 'Annotation ID',
                        model: 'annotation_id',
                        expertOnly: true,
                        visible: this.fieldVisible
                    },
                    {
                        type: 'input',
                        inputType: 'text',
                        label: 'Text',
                        model: 'annotation_text',
                    },
                    defaultAnnotationType !== 'language' ? this.createSelect('Type',
                        {
                            model: 'annotation_type',
                            styleClasses: 'mbottom-large',
                            default: (defaultAnnotationType === 'language' ? defaultAnnotationType : null )
                            // selectOptions: defaultType === 'language' ? { name: 'language' } : {}
                        }
                    ) : {
                        type: 'input',
                        inputType: 'hidden',
                        value: defaultAnnotationType,
                        default: defaultAnnotationType,
                        model: 'annotation_type'
                    },

                    // language
                    ...['language_codeswitchingType', 'language_codeswitchingRank', 'language_codeswitchingDomain', 'language_codeswitchingFormulaicity',
                        'language_bigraphismType', 'language_bigraphismRank', 'language_bigraphismDomain', 'language_bigraphismFormulaicity'].map(
                        field => [
                            this.createOperators(field + '_op', {
                                visible: this.annotationFieldVisible,
                                collapsible: true,
                                collapsed: this.groupCollapsed
                            }),
                            this.createMultiSelect(this.formatPropertyLabel(field, 'language'), {
                                model: field,
                                visible: defaultAnnotationType === 'language' ? true : this.annotationFieldVisible
                            }),
                        ].filter( item => item !== null)
                    ).flat(Infinity),

                    // typography
                    ...[
                        'typography_punctuation', 'typography_accentuation', 'typography_symbol', 'typography_accronym', 'typography_vacat', // @todo: mbottom-large after
                        'typography_correction', 'typography_insertion', 'typography_deletion', 'typography_wordSplitting', 'typography_abbreviation', // @todo: mbottom-large after
                        'typography_positionInText', 'typography_wordClass'
                    ].map(
                        field => [
                            this.createOperators(field + '_op', {
                                visible: this.annotationFieldVisible,
                                collapsible: true,
                                collapsed: this.groupCollapsed
                            }),
                            this.createMultiSelect(this.formatPropertyLabel(field, 'typography'), {
                                model: field,
                                visible: this.annotationFieldVisible
                            }),
                        ]
                    ).flat(Infinity),

                    // lexis
                    ...['lexis_standardForm', 'lexis_type', 'lexis_subtype', 'lexis_wordclass', 'lexis_formulaicity',
                        'lexis_prescription', 'lexis_proscription', 'lexis_identifier'].map(
                        field => [
                            this.createOperators(field + '_op', {
                                collapsible: true,
                                collapsed: this.groupCollapsed,
                                visible: this.annotationFieldVisible
                            }),
                            this.createMultiSelect(
                                this.formatPropertyLabel(field, 'lexis'),
                                {
                                    model: field,
                                    styleClasses: 'field__' + field,
                                    visible: this.annotationFieldVisible
                                }
                            ),
                        ]
                    ).flat(Infinity),

                    // orthography
                    ...['orthography_standardForm', 'orthography_type', 'orthography_subtype', 'orthography_wordclass', 'orthography_formulaicity', 'orthography_positionInWord'].map(
                        field => [
                            this.createOperators(field + '_op', {
                                collapsible: true,
                                collapsed: this.groupCollapsed,
                                visible: this.annotationFieldVisible
                            }),
                            this.createMultiSelect(this.formatPropertyLabel(field, 'orthography'), {
                                model: field,
                                visible: this.annotationFieldVisible
                            }),
                        ]
                    ).flat(Infinity),

                    // morphology
                    ...['morphology_standardForm', 'morphology_type', 'morphology_subtype', 'morphology_wordclass', 'morphology_formulaicity', 'morphology_positionInWord'].map(
                        field => [
                            this.createOperators(field + '_op', {
                                collapsible: true,
                                collapsed: this.groupCollapsed,
                                visible: this.annotationFieldVisible
                            }),
                            this.createMultiSelect(this.formatPropertyLabel(field, 'morphology'), {
                                model: field,
                                visible: this.annotationFieldVisible
                            }),
                        ]
                    ).flat(Infinity),

                    // morpho-syntactical
                    ...['morpho_syntactical_coherenceForm', 'morpho_syntactical_coherenceContent', 'morpho_syntactical_coherenceContext',
                        'morpho_syntactical_complementationForm', 'morpho_syntactical_complementationContent', 'morpho_syntactical_complementationContext',
                        'morpho_syntactical_subordinationForm', 'morpho_syntactical_subordinationContent', 'morpho_syntactical_subordinationContext',
                        'morpho_syntactical_relativisationForm', 'morpho_syntactical_relativisationContent', 'morpho_syntactical_relativisationContext',
                        'morpho_syntactical_typeFormulaicity', 'morpho_syntactical_typeReconstruction'
                    ].map(
                        field => [
                            this.createOperators(field + '_op', {
                                collapsible: true,
                                collapsed: this.groupCollapsed,
                                visible: this.annotationFieldVisible
                            }),
                            this.createMultiSelect(this.formatPropertyLabel(field, 'morpho_syntactical'), {
                                model: field,
                                visible: this.annotationFieldVisible
                                // visible: true,
                            }),
                        ]
                    ).flat(Infinity),

                ].filter( item => item !== null)
            }
        },
        handshiftFields(expertOnly = false) {
            return {
                expertOnly: expertOnly,
                visible: this.fieldVisible,
                collapsible: true,
                collapsed: this.groupCollapsed,
                legend: 'Handwriting',
                id: 'handshift',
                fields: [
                    // handshift
                    this.createMultiSelect('Script Type', {model: 'handshift_scriptType'}),
                    this.createMultiSelect('Degree of Formality', {model: 'handshift_degreeOfFormality'}),
                    this.createMultiSelect('Expansion', {model: 'handshift_expansion'}),
                    this.createMultiSelect('Slope', {model: 'handshift_slope'}),
                    this.createMultiSelect('Curvature', {model: 'handshift_curvature'}),
                    this.createMultiSelect('Connectivity', {model: 'handshift_connectivity'}),
                    this.createMultiSelect('Orientation', {model: 'handshift_orientation'}),
                    this.createMultiSelect('Regularity', {model: 'handshift_regularity'}),
                    this.createMultiSelect('Lineation', {model: 'handshift_lineation', styleClasses: 'mbottom-large'}),
                    this.createMultiSelect('Punctuation', {model: 'handshift_punctuation'}),
                    this.createMultiSelect('Accentuation', {model: 'handshift_accentuation'}),
                    this.createMultiSelect('Word splitting', {model: 'handshift_wordSplitting'}),
                    this.createMultiSelect('Abbreviation', {model: 'handshift_abbreviation'}),
                    this.createMultiSelect('Correction', {model: 'handshift_correction'}),
                    this.createMultiSelect('Status', {model: 'handshift_status'})
                ]
            }
        },
        genericStructureFields(expertOnly = false) {
            return {
                expertOnly: expertOnly,
                visible: this.fieldVisible,
                collapsible: true,
                collapsed: this.groupCollapsed,
                legend: 'Generic structure',
                id: 'gts',
                fields: [
                    // level
                    this.createMultiSelect('Level', {model: 'textLevel'}),
                    this.createMultiSelect('Part', {model: 'gts_part'}),
                    this.createMultiSelect('Type', {model: 'gtsa_type'}),
                    this.createMultiSelect('Subtype', {model: 'gtsa_subtype', 'dependency': 'gtsa_type'}),
                    this.createMultiSelect('Speech act', {model: 'gtsa_speechAct'}),
                    this.createMultiSelect('Information status', {model: 'gtsa_informationStatus'}),
                    this.createMultiSelect('Standard form', {model: 'gtsa_standardForm'}),
                    this.createMultiSelect('Attached to', {model: 'gtsa_attachedTo'}),
                    this.createMultiSelect('Type Attachment', {model: 'gtsa_attachmentType'}),

                ]
            }
        },
        genericStructureFieldsAnnotations(expertOnly = false) {
            return {
                expertOnly: expertOnly,
                visible: this.fieldVisible,
                collapsible: true,
                collapsed: this.groupCollapsed,
                legend: 'Generic structure',
                id: 'gtsa',
                fields: [
                    // level
                    this.createMultiSelect('Level', {model: 'gts_textLevel'}),
                    this.createMultiSelect('Part', {model: 'gts_part'}),

                    this.createMultiSelect('Type', {model: 'gtsa_type'}),
                    this.createMultiSelect('Subtype', {model: 'gtsa_subtype', 'dependency': 'gtsa_type'}),
                    this.createMultiSelect('Speech act', {model: 'gtsa_speechAct'}),
                ]
            }
        },
        layoutStructureFields(expertOnly = false) {
            return {
                expertOnly: expertOnly,
                visible: this.fieldVisible,
                collapsible: true,
                collapsed: this.groupCollapsed,
                legend: 'Layout structure',
                id: 'lts',
                fields: [
                    this.createMultiSelect('Part', {model: 'lts_part'}),
                    this.createMultiSelect('Type', {model: 'ltsa_type'}),
                    this.createMultiSelect('Subtype', {model: 'ltsa_subtype', 'dependency': 'ltsa_type'}),
                    this.createMultiSelect('Spacing', {model: 'ltsa_spacing'}),
                    this.createMultiSelect('Separation', {model: 'ltsa_separation'}),
                    this.createMultiSelect('Orientation', {model: 'ltsa_orientation'}),
                    this.createMultiSelect('Alignment', {model: 'ltsa_alignment'}),
                    this.createMultiSelect('Indentation', {model: 'ltsa_indentation'}),
                    this.createMultiSelect('Lectional signs', {model: 'ltsa_lectionalSigns'}),
                    this.createMultiSelect('Lineation', {model: 'ltsa_lineation'}),
                    this.createMultiSelect('Pagination', {model: 'ltsa_pagination'}),
                ]
            }
        },
        charachterRecognitionToolFields(expertOnly = false) {
            return{
                expertOnly: expertOnly,
                visible: this.fieldVisible,
                collapsible: true,
                collapsed: this.groupCollapsed,
                legend: 'Character recognition',
                id: 'crt',
                fields: [
                    this.createRangeSlider('arabic_relative', 'Arabic (relative)', 0, 100, 0.1, 1, '%'),
                    this.createRangeSlider('coptic_relative', 'Coptic (relative)', 0, 100, 0.1,1, '%'),
                    this.createRangeSlider('greek_relative', 'Greek (relative)', 0, 100, 0.1, 1, '%'),
                    this.createRangeSlider('latin_relative', 'Latin (relative)', 0, 100, 0.1, 1, '%'),
                ]
            }
        },

        /* helpers */
        formatPropertyLabel(field, prefix) {
            field = prefix ? field.replace(prefix + '_', '') : field
            return Case.sentence(field)
        },

        /* hide expertOnly groups/fields */
        fieldVisible(model, field) {
            // check export mode
            if ( field?.expertOnly && !this?.config?.expertMode ) {
                return false
            }

            return true
        },

        annotationFieldVisible(model, field) {
            // check export mode
            if ( field?.expertOnly && !this?.config?.expertMode ) {
                return false
            }

            // check annotation type
            if ( model?.annotation_type?.id ) {
                return field.model.startsWith(model.annotation_type.id);
            }

            return false
        },
        typographyFieldVisible(model, field) {
            return model?.annotation_type?.id === "typography"
        }

    }
}
