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

import fieldRadio from '../Components/FormFields/fieldRadio'

import CollapsibleGroups from '../Components/Search/CollapsibleGroups'
import ExpertGroups from '../Components/Search/ExpertGroups'
import SharedSearch from "../Components/Search/SharedSearch";
import PersistentConfig from "../Components/Shared/PersistentConfig";

Vue.component('fieldRadio', fieldRadio);

export default {
    components: {
        CheckboxSwitch
    },
    mixins: [
        AbstractField,
        AbstractSearch,
        PersistentConfig('TextSearchConfig'),
        SharedSearch,
        CollapsibleGroups(),
        ExpertGroups(),
    ],
    props: {
    },
    data() {
        let data = {
            model: {
                date_search_type: 'exact',
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
                            this.createMultiSelect('Keyword',
                                {
                                    model: 'keyword'
                                }
                            ),
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
                            this.createMultiSelect('Material',
                                {
                                    model: 'material'
                                },
                            ),
                        ]
                    },
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
                            this.createSelect('Text subtype', {model: 'text_subtype'}),
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
                    {
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
                'sortable': ['id', 'tm_id','title', 'year_begin', 'year_end'],
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
            let columns = ['id', 'tm_id', 'title','year_begin','year_end']
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