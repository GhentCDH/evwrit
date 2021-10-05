<template>
    <div>
        <aside class="col-sm-3">
            <div class="bg-tertiary padding-default">
                <div
                        v-if="showReset"
                        class="form-group"
                >
                    <button
                            class="btn btn-block"
                            @click="resetAllFilters"
                    >
                        Reset all filters
                    </button>
                </div>
                <vue-form-generator
                        ref="form"
                        :model="model"
                        :options="formOptions"
                        :schema="schema"
                        @validated="onValidated"
                        @model-updated="modelUpdated"
                />
            </div>
        </aside>
        <article class="col-sm-9 search-page">
            <div
                    v-if="countRecords"
                    class="count-records"
            >
                <h6>{{ countRecords }}</h6>
            </div>
            <v-server-table
                    ref="resultTable"
                    :columns="tableColumns"
                    :options="tableOptions"
                    :url="urls['search_api']"
                    @data="onData"
                    @loaded="onLoaded"
            >
                <template slot="title" slot-scope="props">
                    <a :href="urls['get_single'].replace('text_id', props.row.id)">
                        {{ props.row.title }}
                    </a>
                </template>
                <template slot="id" slot-scope="props">
                    <a :href="urls['get_single'].replace('text_id', props.row.id)">
                        {{ props.row.id }}
                    </a>
                </template>
                <template slot="tm_id" slot-scope="props">
                    <a :href="urls['get_single'].replace('text_id', props.row.id)">
                        {{ props.row.tm_id }}
                    </a>
                </template>
                <template slot="annotations" slot-scope="props">
                    <template v-for="(annotations, type) in props.row.annotations">
                        <template v-for="annotation in annotations" class="text-result">
                            <GreekText
                                    :text="annotation.context.text"
                                    :annotations="[ [annotation.text_selection.selection_start, annotation.text_selection.selection_end - 1, { id: annotation.id, type: annotation.type, class: 'annotation annotation-' + annotation.type }] ]"
                                    :annotationOffset="annotation.context.start"
                                    :compact="true">
                            </GreekText>
                        </template>
                    </template>
                </template>
            </v-server-table>
        </article>
        <div
                v-if="openRequests"
                class="loading-overlay"
        >
            <div class="spinner"/>
        </div>
    </div>
</template>
<script>
import Vue from 'vue'
import VueFormGenerator from 'vue-form-generator'

import AbstractField from '../Components/FormFields/AbstractField'
import AbstractSearch from '../Components/Search/AbstractSearch'
import CollapsibleGroups from '../Components/Search/CollapsibleGroups'

import fieldRadio from '../Components/FormFields/fieldRadio'
import GreekText from '../Components/Shared/GreekText'

Vue.component('fieldRadio', fieldRadio);


export default {
    components: {
        GreekText
    },
    mixins: [
        AbstractField,
        AbstractSearch,
        CollapsibleGroups('annotation-search-groups')
    ],
    props: {
    },
    data() {
        let data = {
            model: {
                date_search_type: 'exact',
                lines: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                columns: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                letters_per_line: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                width: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                height: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
            },
            persons: null,
            schema: {
                groups: [
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Physical information',
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
                            this.createMultiSelect('Keyword', { model: 'keyword'}),
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
                            this.createMultiSelect('Era',{ model: 'era' } ),
                            this.createMultiSelect('Language', { model: 'language' } ),
                        ]
                    },
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Communicative information',
                        fields: [
                            this.createSelect('Text type', {model: 'text_type'}),
                            this.createSelect('Text subtype', {model: 'text_subtype' , dependency: 'text_type' }),
                            this.createMultiSelect('Social distance', { model: 'social_distance' }),
                            this.createSelect('Generic agentive role', {model: 'generic_agentive_role'}),
                            this.createSelect('Agentive role', {model: 'agentive_role', 'dependency': 'generic_agentive_role'}),
                            this.createSelect('Generic communicative goal', {model: 'generic_communicative_goal'}),
                            this.createSelect('Communicative goal', {model: 'communicative_goal', 'dependency': 'generic_communicative_goal'}),
                        ]
                    },
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Materiality',
                        fields: [
                            this.createMultiSelect('Production stage', { model: 'production_stage' }),
                            this.createMultiSelect('Material',{ model: 'material' }),
                            this.createMultiSelect('Format', { model: 'text_format'}),
                            this.createMultiSelect('Writing direction', { model: 'writing_direction' }),
                            this.createSelect('Recto', { model: 'is_recto' } ),
                            this.createSelect('Verso', { model: 'is_verso' } ),
                            this.createSelect('Transversa charta', { model: 'is_transversa_charta' } ),
                            this.createRangeSlider('lines','Text lines',0,160,5),
                            this.createRangeSlider('columns','Text columns',0,10,1),
                            this.createRangeSlider('letters_per_line','Letters per line',0,220,5),
                            this.createRangeSlider('width','Width',0,320,5),
                            this.createRangeSlider('height','Height',0,300,5),
                        ]
                    },
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Typography annotations',
                        fields: [
                            this.createMultiSelect('Word splitting', { model: 'typography_wordSplitting' }),
                            this.createMultiSelect('Correction', { model: 'typography_correction' }),
                            this.createMultiSelect('Insertion', { model: 'typography_insertion' }),
                            this.createMultiSelect('Abbreviation', { model: 'typography_abbreviation' }),
                            this.createMultiSelect('Deletion', { model: 'typography_deletion' }),
                            this.createMultiSelect('Symbol', { model: 'typography_symbol' }),
                            this.createMultiSelect('Punctuation', { model: 'typography_punctuation' }),
                            this.createMultiSelect('Accentuation', { model: 'typography_accentuation' }),
                        ]
                    },
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Lexis annotations',
                        fields: [
                            this.createMultiSelect('Standard form', { model: 'lexis_standardForm' }),
                            this.createMultiSelect('Type', { model: 'lexis_type' }),
                            this.createMultiSelect('Subtype', { model: 'lexis_subtype' }),
                            this.createMultiSelect('Wordclass', { model: 'lexis_wordclass' }),
                            this.createMultiSelect('Formulaicity', { model: 'lexis_formulaicity' }),
                            this.createMultiSelect('Prescription', { model: 'lexis_prescription' }),
                            this.createMultiSelect('Proscription', { model: 'lexis_proscription' }),
                            this.createMultiSelect('Identifier', { model: 'lexis_identifier' }),
                        ]
                    },
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Orthography annotations',
                        fields: [
                            this.createMultiSelect('Standard form', { model: 'orthography_standardForm' }),
                            this.createMultiSelect('Type', { model: 'orthography_type' }),
                            this.createMultiSelect('Subtype', { model: 'orthography_subtype' }),
                            this.createMultiSelect('Wordclass', { model: 'orthography_wordclass' }),
                            this.createMultiSelect('Formulaicity', { model: 'orthography_formulaicity' }),
                            this.createMultiSelect('Position in word', { model: 'orthography_positionInWord' }),
                        ]
                    },
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Language annotations',
                        fields: [
                            this.createMultiSelect('Codeswitching', { model: 'language_codeswitchingType' }),
                            this.createMultiSelect('Codeswitching rank', { model: 'language_codeswitchingRank' }),
                            this.createMultiSelect('Codeswitching domain', { model: 'language_codeswitchingDomain' }),
                            this.createMultiSelect('Codeswitching formulaicity', { model: 'language_codeswitchingFormulaicity' }),
                            this.createMultiSelect('Bigraphism', { model: 'language_bigraphismType' }),
                            this.createMultiSelect('Bigraphism rank', { model: 'language_bigraphismRank' }),
                            this.createMultiSelect('Bigraphism domain', { model: 'language_bigraphismDomain' }),
                            this.createMultiSelect('Bigraphism formulaicity', { model: 'language_bigraphismFormulaicity' }),
                        ]
                    },
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Morpho-Syntactical annotations',
                        fields: [
                            this.createMultiSelect('Coherence form', { model: 'morpho_syntactical_coherenceForm' }),
                            this.createMultiSelect('Coherence content', { model: 'morpho_syntactical_coherenceContent' }),
                            this.createMultiSelect('Coherence context', { model: 'morpho_syntactical_coherenceContext' }),
                            this.createMultiSelect('Complementation form', { model: 'morpho_syntactical_complementationForm' }),
                            this.createMultiSelect('Complementation content', { model: 'morpho_syntactical_complementationContent' }),
                            this.createMultiSelect('Complementation context', { model: 'morpho_syntactical_complementationContext' }),
                            this.createMultiSelect('Subordination form', { model: 'morpho_syntactical_subordinationForm' }),
                            this.createMultiSelect('Subordination content', { model: 'morpho_syntactical_subordinationContent' }),
                            this.createMultiSelect('Subordination context', { model: 'morpho_syntactical_subordinationContext' }),
                            this.createMultiSelect('Relativisation form', { model: 'morpho_syntactical_relativisationForm' }),
                            this.createMultiSelect('Relativisation content', { model: 'morpho_syntactical_relativisationContent' }),
                            this.createMultiSelect('Relativisation context', { model: 'morpho_syntactical_relativisationContext' }),
                        ]
                    },
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Administrative information',
                        fields: [
                            this.createSelect('Project', {model: 'project'}),
                            this.createSelect('Collaborator', {model: 'collaborator'}),
                        ]
                    }
                ],
            },
            tableOptions: {
                headings: {
                    id: 'ID',
                    tm_id: 'Tm ID ',
                    title: 'Title',
                    annotations: 'Annotations'
                },
                columnsClasses: {
                    id: 'vue-tables__col vue-tables__col--id',
                    tm_id: 'vue-tables__col vue-tables__col--tm-id',
                    title: 'vue-tables__col vue-tables__col--title'
                },
                'filterable': false,
                'orderBy': {
                    'column': 'title'
                },
                'perPage': 25,
                'perPageValues': [25, 50, 100],
                'sortable': ['id', 'tm_id', 'title'],
                customFilters: ['filters'],
                requestFunction: AbstractSearch.requestFunction,
                rowClassCallback: function (row) {
                    return '';
                    // return (row.public == null || row.public) ? '' : 'warning'
                },
            },
            submitModel: {
                submitType: 'text',
                person: {},
            },
            defaultOrdering: 'title',
            groupIsOpen: []
        }

        return data
    },
    computed: {
        tableColumns() {
            let columns = ['id', 'tm_id', 'title', 'annotations']
            return columns
        },
    },
    watch: {
        defaultOrdering: function(val) {
            console.log(val)
        },
    },
    methods: {
        update() {
            // Don't create a new history item
            this.noHistory = true;
            this.$refs.resultTable.refresh();
        },
    },

}
</script>