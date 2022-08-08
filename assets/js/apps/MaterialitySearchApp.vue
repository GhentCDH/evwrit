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
                            <li><a :href="getUrl('export_csv') + '?' + querystring">Export as CSV</a></li>
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

import fieldRadio from '../components/FormFields/fieldRadio'

import CollapsibleGroups from '../components/Search/CollapsibleGroups'
import ExpertGroups from '../components/Search/ExpertGroups'
import PersistentConfig from "../components/Shared/PersistentConfig";
import SharedSearch from "../components/Search/SharedSearch";


Vue.component('fieldRadio', fieldRadio);

export default {
    components: {
        CheckboxSwitch
    },
    mixins: [
        PersistentConfig('MaterialitySearchConfig'),
        AbstractField,
        AbstractSearch,
        SharedSearch,
        ExpertGroups,
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
                            this.createMultiSelect('Keyword',
                                {
                                    model: 'keyword'
                                },
                                {
                                    multiple: true,
                                    closeOnSelect: false,
                                }
                            ),
                        ]
                    },
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Communicative information',
                        fields: [
                            this.createMultiSelect('Text type', {model: 'text_type'}),
                            this.createSelect('Text subtype', {model: 'text_subtype', 'dependency': 'text_type'}),
                            this.createMultiSelect('Social distance',
                                {
                                    model: 'social_distance'
                                },
                                {
                                    multiple: true,
                                    closeOnSelect: false,
                                }
                            ),
                            this.createMultiSelect('Generic agentive role', {model: 'generic_agentive_role'}),
                            this.createMultiSelect('Agentive role', {model: 'agentive_role', 'dependency': 'generic_agentive_role'}),
                            this.createMultiSelect('Generic communicative goal', {model: 'generic_communicative_goal'}),
                            this.createMultiSelect('Communicative goal', {model: 'communicative_goal', 'dependency': 'generic_communicative_goal'}),
                        ]
                    },
                    {
                        expertOnly: true,
                        styleClasses: 'collapsible collapsed',
                        legend: 'Administrative information',
                        fields: [
                            this.createMultiSelect('Project',
                                {
                                    model: 'project'
                                },
                                {
                                    multiple: true,
                                    closeOnSelect: false,
                                }
                            ),
                            this.createMultiSelect('Collaborator', {model: 'collaborator'}),
                        ]
                    }
                ],
            },
            tableOptions: {
                headings: {
                },
                columnsClasses: {
                    name: 'no-wrap',
                },
                'filterable': false,
                'orderBy': {
                    'column': 'title'
                },
                'perPage': 25,
                'perPageValues': [25, 50, 100],
                'sortable': ['title', 'year_begin', 'year_end'],
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
        }

        // Add view internal only fields
        if (this.isViewInternal) {
        }

        return data
    },
    computed: {
        tableColumns() {
            let columns = ['id', 'tm_id', 'title', 'text_type', 'location_found','year_begin','year_end']
            return columns
        },
    },
    watch: {},
    methods: {
        update() {
            // Don't create a new history item
            this.noHistory = true;
            this.$refs.resultTable.refresh();
        },
    },
}
</script>