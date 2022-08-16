import Vue from 'vue'
import AbstractSearch from './AbstractSearch'
import VueFormGenerator from 'vue-form-generator'

import AbstractField from '../FormFields/AbstractField'
import CheckboxSwitch from '../FormFields/CheckboxSwitch'

import fieldRadio from '../FormFields/fieldRadio'

import ExpertGroups from './ExpertGroups'
import SharedSearch from "./SharedSearch"

Vue.component('fieldRadio', fieldRadio);

export default {
    methods: {
        generalInformationFields(includeMaterial = false) {
            return {
                styleClasses: 'collapsible collapsed',
                legend: 'General information',
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
                    includeMaterial ?
                    this.createMultiSelect('Material',
                        {
                            model: 'material',
                        },
                    ) : {},
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
                styleClasses: 'collapsible collapsed',
                legend: 'Communicative information',
                fields: [
                    this.createMultiSelect('Archive',
                        {
                            model: 'archive'
                        },
                    ),
                    this.createSelect('Text type', {model: 'text_type'}),
                    this.createSelect('Text subtype', {model: 'text_subtype', 'dependency': 'text_type'}),
                    this.createMultiSelect('Social distance',
                        {
                            model: 'social_distance'
                        },
                    ),
                    this.createSelect('Generic agentive role', {model: 'generic_agentive_role'}),
                    this.createSelect('Agentive role', {model: 'agentive_role', 'dependency': 'generic_agentive_role'}),
                    this.createSelect('Generic communicative goal', {model: 'generic_communicative_goal'}),
                    this.createSelect('Communicative goal', {model: 'communicative_goal', 'dependency': 'generic_communicative_goal'}),
                ]
            }
        },
        ancientPersonFields() { 
            return {
                styleClasses: 'collapsible collapsed',
                legend: 'Ancient persons',
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
                styleClasses: 'collapsible collapsed',
                legend: 'Administrative information',
                fields: [
                    this.createMultiSelect('Project',
                        {
                            model: 'project'
                        },
                    ),
                    this.createMultiSelect('Collaborator', {model: 'collaborator'}),
                ]
            }
        },
        materialityFields() {
            return {
                styleClasses: 'collapsible collapsed',
                legend: 'Materiality',
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
                    this.createMultiSelect('Recto', { model: 'is_recto' } ),
                    this.createMultiSelect('Verso', { model: 'is_verso' } ),
                    this.createMultiSelect('Transversa charta', { model: 'is_transversa_charta' } ),
                    this.createRangeSlider('lines','Text lines',0,160,5),
                    this.createRangeSlider('columns','Text columns',0,10,1),
                    this.createRangeSlider('letters_per_line','Letters per line',0,220,5),
                    this.createRangeSlider('width','Width',0,320,5),
                    this.createRangeSlider('height','Height',0,300,5),
                ]
            }
        },
        annotationsFields() {
            return {
                styleClasses: 'collapsible collapsed bg-tertiary',
                legend: 'Annotations',
                fields: [
                    this.createSelect('Type', { model: 'annotation_type' } ),
                    // language
                    this.createMultiSelect('Codeswitching', { model: 'language_codeswitchingType' }),
                    this.createMultiSelect('Codeswitching rank', { model: 'language_codeswitchingRank' }),
                    this.createMultiSelect('Codeswitching domain', { model: 'language_codeswitchingDomain' }),
                    this.createMultiSelect('Codeswitching formulaicity', { model: 'language_codeswitchingFormulaicity' }),
                    this.createMultiSelect('Transliteration', { model: 'language_bigraphismType' }),
                    this.createMultiSelect('Transliteration rank', { model: 'language_bigraphismRank' }),
                    this.createMultiSelect('Transliteration domain', { model: 'language_bigraphismDomain' }),
                    this.createMultiSelect('Transliteration formulaicity', { model: 'language_bigraphismFormulaicity' }),
                    // typography
                    this.createMultiSelect('Punctuation', { model: 'typography_punctuation' }),
                    this.createMultiSelect('Accentuation', { model: 'typography_accentuation' }),
                    this.createMultiSelect('Symbol', { model: 'typography_symbol'}),
                    this.createMultiSelect('Acronym', { model: 'typography_accronym' }),
                    this.createMultiSelect('Vacat', { model: 'typography_vacat', styleClasses:'mbottom-large' }),
                    this.createMultiSelect('Correction', { model: 'typography_correction' }),
                    this.createMultiSelect('Insertion', { model: 'typography_insertion' }),
                    this.createMultiSelect('Deletion', { model: 'typography_deletion' }),
                    this.createMultiSelect('Word splitting', { model: 'typography_wordSplitting' }),
                    this.createMultiSelect('Abbreviation', { model: 'typography_abbreviation', styleClasses:'mbottom-large' }),
                    this.createMultiSelect('Position in text', { model: 'typography_positionInText' }),
                    this.createMultiSelect('Word class', { model: 'typography_wordClass' }),
                    // lexis
                    this.createMultiSelect('Standard form', { model: 'lexis_standardForm' }),
                    this.createMultiSelect('Type', { model: 'lexis_type' }),
                    this.createMultiSelect('Subtype', { model: 'lexis_subtype' }),
                    this.createMultiSelect('Wordclass', { model: 'lexis_wordclass' }),
                    this.createMultiSelect('Formulaicity', { model: 'lexis_formulaicity' }),
                    this.createMultiSelect('Prescription', { model: 'lexis_prescription' }),
                    this.createMultiSelect('Proscription', { model: 'lexis_proscription' }),
                    this.createMultiSelect('Identifier', { model: 'lexis_identifier' }),
                    // orthography
                    this.createMultiSelect('Standard form', { model: 'orthography_standardForm' }),
                    this.createMultiSelect('Type', { model: 'orthography_type' }),
                    this.createMultiSelect('Subtype', { model: 'orthography_subtype' }),
                    this.createMultiSelect('Wordclass', { model: 'orthography_wordclass' }),
                    this.createMultiSelect('Formulaicity', { model: 'orthography_formulaicity' }),
                    this.createMultiSelect('Position in word', { model: 'orthography_positionInWord' }),
                    // morphology
                    this.createMultiSelect('Standard form', { model: 'morphology_standardForm' }),
                    this.createMultiSelect('Type', { model: 'morphology_type' }),
                    this.createMultiSelect('Subtype', { model: 'morphology_subtype' }),
                    this.createMultiSelect('Wordclass', { model: 'morphology_wordclass' }),
                    this.createMultiSelect('Formulaicity', { model: 'morphology_formulaicity' }),
                    this.createMultiSelect('Position in word', { model: 'morphology_positionInWord' }),
                    // morpho-syntactical
                    this.createMultiSelect('Coherence form', { model: 'morpho_syntactical_coherenceForm' }),
                    this.createMultiSelect('Coherence content', { model: 'morpho_syntactical_coherenceContent' }),
                    this.createMultiSelect('Coherence context', { model: 'morpho_syntactical_coherenceContext' }),
                    this.createMultiSelect('Complementation form', { model: 'morpho_syntactical_complementationForm' }),
                    this.createMultiSelect('Complementation content', { model: 'morpho_syntactical_complementationContent' }),
                    this.createMultiSelect('Complementation context', { model: 'morpho_syntactical_complementationContext' }),
                    this.createMultiSelect('Subordination form', { model: 'morpho_syntactical_subordinationForm' }),
                    this.createMultiSelect('Subordination content', { model: 'morpho_syntactical_subordinationContent' }),
                    this.createMultiSelect('Subordination context', { model: 'morpho_syntactical_subordinationContext' }),
                    this.createMultiSelect('Relativisation form', { model: 'morpho_syntactical_orderForm' }), // todo: use to relativisationForm after schema update
                    this.createMultiSelect('Relativisation content', { model: 'morpho_syntactical_orderContent' }), // todo: use to relativisationContent after schema update
                    this.createMultiSelect('Relativisation context', { model: 'morpho_syntactical_orderContext' }), // todo: use to relativisationContext after schema update
                ]
            }
        },
        handshiftFields() {
            return {
                styleClasses: 'collapsible collapsed',
                legend: 'Handwriting',
                fields: [
                    // handshift
                    this.createMultiSelect('Script Type', { model: 'handshift_scriptType' }),
                    this.createMultiSelect('Degree of Formality', { model: 'handshift_degreeOfFormality' }),
                    this.createMultiSelect('Expansion', { model: 'handshift_expansion' }),
                    this.createMultiSelect('Slope', { model: 'handshift_slope' }),
                    this.createMultiSelect('Curvature', { model: 'handshift_curvature' }),
                    this.createMultiSelect('Connectivity', { model: 'handshift_connectivity' }),
                    this.createMultiSelect('Orientation', { model: 'handshift_orientation' }),
                    this.createMultiSelect('Regularity', { model: 'handshift_regularity' }),
                    this.createMultiSelect('Lineation', { model: 'handshift_lineation', styleClasses: 'mbottom-large' }),
                    this.createMultiSelect('Punctuation', { model: 'handshift_punctuation' }),
                    this.createMultiSelect('Accentuation', { model: 'handshift_accentuation' }),
                    this.createMultiSelect('Word splitting', { model: 'handshift_wordSplitting' }),
                    this.createMultiSelect('Abbreviation', { model: 'handshift_abbreviation' }),
                    this.createMultiSelect('Correction', { model: 'handshift_correction' }),
                    this.createMultiSelect('Status', { model: 'handshift_status' })
                ]
            }
        },
        genericStructureFields() {
            return {
                styleClasses: 'collapsible collapsed',
                legend: 'Generic structure',
                fields: [
                    // level
                    this.createMultiSelect('Level', { model: 'gts_textLevel' }),
                    this.createMultiSelect('Part', { model: 'gts_part' }),
                    this.createMultiSelect('Type', { model: 'gtsa_type' }),
                    this.createMultiSelect('Subtype', { model: 'gtsa_subtype', 'dependency': 'gtsa_type' }),
                    this.createMultiSelect('Speech act', { model: 'gtsa_speechAct' }),
                    this.createMultiSelect('Information status', { model: 'gtsa_informationStatus' }),
                    this.createMultiSelect('Standard form', { model: 'gtsa_standardForm' }),
                    this.createMultiSelect('Attached to', { model: 'gtsa_attachedTo' }),
                    this.createMultiSelect('Type Attachment', { model: 'gtsa_attachmentType' }),

                ]
            }
        },
        layoutStructureFields() {
            return {
                styleClasses: 'collapsible collapsed',
                legend: 'Layout structure',
                fields: [
                    this.createMultiSelect('Part', { model: 'lts_part' }),
                    this.createMultiSelect('Type', { model: 'ltsa_type' }),
                    this.createMultiSelect('Subtype', { model: 'ltsa_subtype', 'dependency': 'ltsa_type' }),
                    this.createMultiSelect('Spacing', { model: 'ltsa_spacing' }),
                    this.createMultiSelect('Separation', { model: 'ltsa_separation' }),
                    this.createMultiSelect('Orientation', { model: 'ltsa_orientation' }),
                    this.createMultiSelect('Alignment', { model: 'ltsa_alignment' }),
                    this.createMultiSelect('Indentation', { model: 'ltsa_indentation' }),
                    this.createMultiSelect('Lectional signs', { model: 'ltsa_lectionalSigns' }),
                    this.createMultiSelect('Lineation', { model: 'ltsa_lineation' }),
                    this.createMultiSelect('Pagination', { model: 'ltsa_pagination' }),
                ]
            }
        },
    }
}
