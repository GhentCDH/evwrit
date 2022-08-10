<template>
    <div class="row">
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
                    :url="getUrl('search_api')"
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
                                    <CheckboxSwitch v-model="config.expertMode" class="switch-primary" label="Advanced mode"></CheckboxSwitch>
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
                    <a :href="getTextUrl(props.row.id, props.index)">
                        {{ props.row.title }}
                    </a>
                </template>
                <template slot="id" slot-scope="props">
                    <a :href="getTextUrl(props.row.id, props.index)">
                        {{ props.row.id }}
                    </a>
                </template>
                <template slot="tm_id" slot-scope="props">
                    <a :href="getTextUrl(props.row.id, props.index)">
                        {{ props.row.tm_id }}
                    </a>
                </template>
                <template slot="text_type" slot-scope="props">
                    <td>
                        {{ props.row.text_type.name }}
                    </td>
                </template>
                <template slot="location_found" slot-scope="props">
                    <td>
                        {{ props.row.location_found[0]?.name }}
                    </td>
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

import AbstractField from '../components/FormFields/AbstractField'
import AbstractSearch from '../components/Search/AbstractSearch'
import CheckboxSwitch from '../components/FormFields/CheckboxSwitch'


import AnnotationDetailsFlat from '../components/Annotations/AnnotationDetailsFlat'

import fieldRadio from '../components/FormFields/fieldRadio'
import GreekText from '../components/Text/GreekText'

import CollapsibleGroups from '../components/Search/CollapsibleGroups'
import ExpertGroups from '../components/Search/ExpertGroups'
import PersistentConfig from "../components/Shared/PersistentConfig";
import SharedSearch from "../components/Search/SharedSearch";

import qs from "qs";

Vue.component('fieldRadio', fieldRadio);

export default {
    components: {
        GreekText,
        AnnotationDetailsFlat,
        CheckboxSwitch
    },
    mixins: [
        PersistentConfig('TextStructureSearchConfig'),
        AbstractField,
        AbstractSearch,
        SharedSearch,
        ExpertGroups,
    ],
    props: {
    },
    data() {
        let data = {
            defaultConfig: {
            },
            model: {
                date_search_type: 'exact',
            },
            schema: {
                groups: [
                    // Generic text structure
                    {
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
                    },
                    // Layout text structure
                    {
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
                    },
                    // Handshift
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Handwriting',
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
                    // General information
                    {
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
                                    model: 'location_written'
                                }
                            ),
                            this.createMultiSelect('Language',
                                {
                                    model: 'language'
                                },
                            ),
                            this.createMultiSelect('Script',
                                {
                                    model: 'script'
                                },
                            ),
                            this.createMultiSelect('Material',
                                {
                                    model: 'material'
                                },
                            ),
                            this.createMultiSelect('Keyword',
                                {
                                    model: 'keyword'
                                }
                            ),
                            {
                                type: 'switch',
                                label: 'Translated',
                                model: 'has_translation',
                            },
                            {
                                type: 'switch',
                                label: 'Has image(s)',
                                model: 'has_image',
                            },
                        ]
                    },
                    // Communicative information
                    {
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
                    },
                    // Materiality
                    {
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
                    },
                    // Administrative information
                    {
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
                ],
            },
            tableOptions: {
                headings: {
                    id: 'ID',
                    tm_id: 'Tm ID ',
                    title: 'Title',
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
            let columns = ['id', 'tm_id', 'title', 'text_type', 'location_found']
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
        update() {
            // Don't create a new history item
            this.noHistory = true;
            this.$refs.resultTable.refresh();
        },
    },
}
</script>

<style lang="scss">
</style>
