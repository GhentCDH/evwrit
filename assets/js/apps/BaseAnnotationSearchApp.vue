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
            <h1 v-if="title" class="mbottom-default">{{ title }}</h1>
            <v-server-table
                    ref="resultTable"
                    :columns="tableColumns"
                    :options="tableOptions"
                    :url="urls['search_api']"
                    @data="onData"
                    @loaded="onLoaded"
                    class="form-group-sm"
            >
                <template slot="afterFilter">
                    <b v-if="countRecords">{{ countRecords }}</b>
                </template>
                <template slot="beforeLimit">
                </template>
                <template slot="afterLimit">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" onclick="event.stopPropagation()">
                            <li>
                                <div class="form-group">
                                    <CheckboxSwitch v-model="config.showAnnotationContext" class="switch-primary" label="Show annotation context"></CheckboxSwitch>
                                </div>
                            </li>
                            <li>
                                <div class="form-group">
                                    <CheckboxSwitch v-model="config.showAnnotationDetails" class="switch-primary" label="Show annotation details"></CheckboxSwitch>
                                </div>
                            </li>
                            <li role="separator" class="divider"></li>
                            <li>
                                <div class="form-group">
                                    <CheckboxSwitch v-model="config.expertMode" class="switch-primary" label="Expert mode"></CheckboxSwitch>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-download"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><a :href="urls.export_csv + '?' + querystring">Export as CSV</a></li>
                        </ul>
                    </div>
                </template>
                <template slot="title" slot-scope="props">
                    <a :href="textUrl(props.row.id, props.index)">
                        {{ props.row.title }}
                    </a>
                </template>
                <template slot="id" slot-scope="props">
                    <a :href="textUrl(props.row.id, props.index)">
                        {{ props.row.id }}
                    </a>
                </template>
                <template slot="tm_id" slot-scope="props">
                    <a :href="textUrl(props.row.id, props.index)">
                        {{ props.row.tm_id }}
                    </a>
                </template>
                <template slot="annotations" slot-scope="props">
                    <div class="annotation-result" v-for="annotation in props.row.annotations">
                        <GreekText
                                v-show="config.showAnnotationContext"
                                :text="annotation.context.text"
                                :annotations="[ [annotation.text_selection.selection_start, annotation.text_selection.selection_end - 1, { id: annotation.id, type: annotation.type, class: 'annotation annotation-' + annotation.type }] ]"
                                :annotationOffset="annotation.context.start + 1"
                                :compact="true">
                        </GreekText>
                        <GreekText
                                v-show="!config.showAnnotationContext"
                                :text="annotation.text_selection.text">
                        </GreekText>
                        <AnnotationDetailsFlat v-show="config.showAnnotationDetails" :annotation="annotation"></AnnotationDetailsFlat>
                    </div>
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
import CheckboxSwitch from '../Components/Shared/CheckboxSwitch'


import AnnotationDetailsFlat from '../Components/Annotations/AnnotationDetailsFlat'

import fieldRadio from '../Components/FormFields/fieldRadio'
import GreekText from '../Components/Shared/GreekText'

import CollapsibleGroups from '../Components/Search/CollapsibleGroups'
import ExpertGroups from '../Components/Search/ExpertGroupsAnnotations'
import PersistentConfig from "../Components/Shared/PersistentConfig";
import qs from "qs";

Vue.component('fieldRadio', fieldRadio);

export default {
    components: {
        GreekText,
        AnnotationDetailsFlat,
        CheckboxSwitch
    },
    mixins: [
        AbstractField,
        AbstractSearch,
        PersistentConfig('BaseAnnotationSearchConfig'),
        CollapsibleGroups(),
        ExpertGroups(),
    ],
    props: {
    },
    data() {
        let data = {
            defaultConfig: {
                showAnnotationContext: true,
                showAnnotationDetails: false
            },
            model: {
                date_search_type: 'exact',
                lines: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                columns: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                letters_per_line: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                width: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                height: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
            },
            schema: {
                groups: [
                    // annotations
                    {
                        styleClasses: 'collapsible collapsed bg-tertiary',
                        legend: 'Annotations',
                        fields: [
                            this.createSelect('Type', { model: 'annotation_type' } ),
                            // typography
                            this.createMultiSelect('Word splitting', { model: 'typography_wordSplitting' }),
                            this.createMultiSelect('Correction', { model: 'typography_correction' }),
                            this.createMultiSelect('Insertion', { model: 'typography_insertion' }),
                            this.createMultiSelect('Abbreviation', { model: 'typography_abbreviation' }),
                            this.createMultiSelect('Deletion', { model: 'typography_deletion' }),
                            this.createMultiSelect('Symbol', { model: 'typography_symbol' }),
                            this.createMultiSelect('Punctuation', { model: 'typography_punctuation' }),
                            this.createMultiSelect('Accentuation', { model: 'typography_accentuation' }),
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
                            // language
                            this.createMultiSelect('Codeswitching', { model: 'language_codeswitchingType' }),
                            this.createMultiSelect('Codeswitching rank', { model: 'language_codeswitchingRank' }),
                            this.createMultiSelect('Codeswitching domain', { model: 'language_codeswitchingDomain' }),
                            this.createMultiSelect('Codeswitching formulaicity', { model: 'language_codeswitchingFormulaicity' }),
                            this.createMultiSelect('Bigraphism', { model: 'language_bigraphismType' }),
                            this.createMultiSelect('Bigraphism rank', { model: 'language_bigraphismRank' }),
                            this.createMultiSelect('Bigraphism domain', { model: 'language_bigraphismDomain' }),
                            this.createMultiSelect('Bigraphism formulaicity', { model: 'language_bigraphismFormulaicity' }),
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
                            this.createMultiSelect('Relativisation form', { model: 'morpho_syntactical_relativisationForm' }),
                            this.createMultiSelect('Relativisation content', { model: 'morpho_syntactical_relativisationContent' }),
                            this.createMultiSelect('Relativisation context', { model: 'morpho_syntactical_relativisationContext' }),
                        ]
                    },
                    // Generic text structure
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Generic text structure',
                        fields: [
                            // level
                            this.createMultiSelect('Level', { model: 'text_level' }),

                            // generic text structure part
                            this.createMultiSelect('Part', { model: 'generic_text_structure_part' }),
                        ]
                    },
                    // Handshift
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Handshift',
                        fields: [
                            // handshift
                            this.createMultiSelect('Abbreviation', { model: 'handshift_abbreviation' }),
                            this.createMultiSelect('Accentuation', { model: 'handshift_accentuation' }),
                            this.createMultiSelect('Connectivity', { model: 'handshift_connectivity' }),
                            this.createMultiSelect('Correction', { model: 'handshift_correction' }),
                            this.createMultiSelect('Curvature', { model: 'handshift_curvature' }),
                            this.createMultiSelect('Degree of Formality', { model: 'handshift_degreeOfFormality' }),
                            this.createMultiSelect('Expansion', { model: 'handshift_expansion' }),
                            this.createMultiSelect('Lineation', { model: 'handshift_lineation' }),
                            this.createMultiSelect('Orientation', { model: 'handshift_orientation' }),
                            this.createMultiSelect('Punctuation', { model: 'handshift_punctuation' }),
                            this.createMultiSelect('Regularity', { model: 'handshift_regularity' }),
                            this.createMultiSelect('Script Type', { model: 'handshift_scriptType' }),
                            this.createMultiSelect('Slope', { model: 'handshift_slope' }),
                            this.createMultiSelect('Word splitting', { model: 'handshift_wordSplitting' }),
                        ]
                    },

                    // physical
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
                    // communicative
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Communicative information',
                        expertOnly: true,
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
                    // materiality
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Materiality',
                        expertOnly: true,
                        fields: [
                            this.createMultiSelect('Production stage', { model: 'production_stage' }),
                            this.createMultiSelect('Material',{ model: 'material' }),
                            this.createMultiSelect('Format', { model: 'text_format'}),
                            this.createMultiSelect('Writing direction', { model: 'writing_direction' }),
                            this.createSelect('Recto', { model: 'is_recto', expertOnly: true } ),
                            this.createSelect('Verso', { model: 'is_verso' } ),
                            this.createSelect('Transversa charta', { model: 'is_transversa_charta' } ),
                            this.createRangeSlider('lines','Text lines',0,160,5),
                            this.createRangeSlider('columns','Text columns',0,10,1),
                            this.createRangeSlider('letters_per_line','Letters per line',0,220,5),
                            this.createRangeSlider('width','Width',0,320,5),
                            this.createRangeSlider('height','Height',0,300,5),
                        ]
                    },
                    // administrative
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Administrative information',
                        expertOnly: true,
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
            annotationFilter: null,
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
        },
        // watch model changes
        model: {
            handler: function(current) {
                this.updateFieldVisibility();
            },
            deep: true
        },
    },
    methods: {
        getAnnotationFilter() {
            if ( this.model.hasOwnProperty('annotation_type') && this.model.annotation_type ) {
                return this.model.annotation_type.id;
            } else {
                return false;
            }
        },
        update() {
            // Don't create a new history item
            this.noHistory = true;
            this.$refs.resultTable.refresh();
        },
        onData(data) {
            this.aggregation = data.aggregation
            // todo: ditch .data?
            this.data.search = data.search
            this.data.filters = data.filters
        },
        searchContextHash(index) {
            return window.btoa(JSON.stringify(
                {
                    urls: {
                        search: this.urls.search,
                        search_api: this.urls.search_api,
                        paginate: this.urls.paginate,
                    },
                    params: qs.parse(window.location.href.split('?',2)[1]) ?? [],
                    index: (this.data.search.page - 1) * this.data.search.limit + index,
                    count: this.data.count
                }
            ))
        },
        textUrl(id, index) {
            return this.urls['text_get_single'].replace('text_id', id) + '?context=' + this.searchContextHash(index)
        }

    },
}
</script>

<style lang="scss">
.annotation-result + .annotation-result {
  padding-top: 5px;
  margin-top: 5px;
  border-top: 1px solid #ccc;
}

.annotation-details {
  margin: 10px 0
}
</style>
