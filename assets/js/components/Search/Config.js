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
        generalInformationFields(includeMaterial = false) {
            return {
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
                            styleClasses: "collapsible collapsed",
                        }
                    ),
                    this.createOperators('language_op'),
                    this.createMultiSelect('Language',
                        {
                            model: 'language'
                        },
                    ),
                    this.createOperators('script_op'),
                    this.createMultiSelect('Script',
                        {
                            model: 'script'
                        },
                    ),
                    this.createMultiSelect('Material',
                        {
                            model: 'material',
                            styleClasses: includeMaterial ? '' : 'hidden',
                        },
                    ),
                    this.createMultiSelect('Keyword',
                        {
                            model: 'keyword'
                        }
                    ),
                ]
            }
        },
        communicativeInformationFields() {
            return {
                collapsible: true,
                collapsed: this.groupCollapsed,
                legend: 'Communicative information',
                id: 'communicativeInformation',
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
        ancientPersonFields() {
            return {
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
                    this.createMultiSelect('Occupation', {model: 'ap_occupation'}),
                    this.createMultiSelect('Social rank', {model: 'ap_social_rank'}),
                    this.createMultiSelect('Honorific epithet', {model: 'ap_honorific_epithet'}),
                    this.createMultiSelect('Type graph', {model: 'ap_graph_type'}),
                ]
            }
        },
        administrativeInformationFields() {
            return {
                expertOnly: true,
                collapsible: true,
                collapsed: this.groupCollapsed,
                visible: this.fieldVisible,
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
        materialityFields() {
            return {
                collapsible: true,
                collapsed: this.groupCollapsed,
                legend: 'Materiality',
                id: 'materiality',
                fields: [
                    this.createMultiSelect('Production stage',
                        {
                            model: 'production_stage'
                        },
                        {
                            multiple: true,
                            closeOnSelect: false,
                        }
                    ),
                    this.createMultiSelect('Material',
                        {
                            model: 'material'
                        },
                        {
                            multiple: true,
                            closeOnSelect: false,
                        }
                    ),
                    this.createMultiSelect('Format',
                        {
                            model: 'text_format'
                        },
                        {
                            multiple: true,
                            closeOnSelect: false,
                        }
                    ),
                    this.createMultiSelect('Writing direction',
                        {
                            model: 'writing_direction'
                        },
                        {
                            multiple: true,
                            closeOnSelect: false,
                        }
                    ),
                    this.createMultiSelect('Recto', {model: 'is_recto'}),
                    this.createMultiSelect('Verso', {model: 'is_verso'}),
                    this.createMultiSelect('Transversa charta', {model: 'is_transversa_charta'}),
                    this.createRangeSlider('lines', 'Text lines', 0, 160, 5),
                    this.createRangeSlider('columns', 'Text columns', 0, 10, 1),
                    this.createRangeSlider('letters_per_line', 'Letters per line', 0, 220, 5),
                    this.createRangeSlider('width', 'Width', 0, 320, 5),
                    this.createRangeSlider('height', 'Height', 0, 300, 5),
                ]
            }
        },
        annotationsFields() {
            return {
                // styleClasses: 'collapsible collapsed bg-tertiary',
                legend: 'Annotations',
                collapsible: true,
                collapsed: this.groupCollapsed,
                id: 'annotations',
                fields: [
                    this.createSelect('Type', {model: 'annotation_type', styleClasses: 'mbottom-large'}),

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
                                visible: this.annotationFieldVisible
                            }),
                        ]
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
                        'morpho_syntactical_orderForm', 'morpho_syntactical_orderContent', 'morpho_syntactical_orderContext'
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
                            }),
                        ]
                    ).flat(Infinity),

                ]
            }
        },
        handshiftFields() {
            return {
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
        genericStructureFields() {
            return {
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
        genericStructureFieldsAnnotations() {
            return {
                collapsible: true,
                collapsed: this.groupCollapsed,
                legend: 'Generic structure',
                id: 'gtsa',
                fields: [
                    // level
                    this.createMultiSelect('Level', {model: 'text_level'}),
                    this.createMultiSelect('Part', {model: 'generic_text_structure_part'}),

                ]
            }
        },
        layoutStructureFields() {
            return {
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
        }

    }
}