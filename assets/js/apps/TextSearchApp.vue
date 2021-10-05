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
                    :url="urls['text_search_api']"
                    @data="onData"
                    @loaded="onLoaded"
            >
                <template slot="title" slot-scope="props">
                    <a :href="urls['text_get_single'].replace('text_id', props.row.id)">
                        {{ props.row.title }}
                    </a>
                </template>
                <template slot="id" slot-scope="props">
                    <a :href="urls['text_get_single'].replace('text_id', props.row.id)">
                        {{ props.row.id }}
                    </a>
                </template>
                <template slot="tm_id" slot-scope="props">
                    <a :href="urls['text_get_single'].replace('text_id', props.row.id)">
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
import CollapsibleGroups from '../Components/Search/CollapsibleGroups'

import fieldRadio from '../Components/FormFields/fieldRadio'

Vue.component('fieldRadio', fieldRadio);

export default {
    mixins: [
        AbstractField,
        AbstractSearch,
        CollapsibleGroups('search-text-groups')
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
                                },
                                {
                                    multiple: true,
                                    closeOnSelect: false,
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
                                {
                                    multiple: true,
                                    closeOnSelect: false,
                                }
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
                                {
                                    multiple: true,
                                    closeOnSelect: false,
                                }
                            ),
                            this.createMultiSelect('Text type', {model: 'text_type'}),
                            this.createMultiSelect('Text subtype', {model: 'text_subtype'}),
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