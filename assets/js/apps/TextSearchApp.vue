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

import fieldRadio from '../Components/FormFields/fieldRadio'

Vue.component('fieldRadio', fieldRadio);

export default {
    mixins: [
        AbstractField,
        AbstractSearch,
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
                        styleClasses: 'collapsible',
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
                                model: 'year_from',
                                min: AbstractSearch.YEAR_MIN,
                                max: AbstractSearch.YEAR_MAX,
                                validator: VueFormGenerator.validators.number,
                            },
                            {
                                type: 'input',
                                inputType: 'number',
                                label: 'Year to',
                                model: 'year_to',
                                min: AbstractSearch.YEAR_MIN,
                                max: AbstractSearch.YEAR_MAX,
                                validator: VueFormGenerator.validators.number,
                            },
                            {
                                type: 'radio',
                                label: 'The person date interval must ... the search date interval:',
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
                        styleClasses: 'collapsible',
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
                        styleClasses: 'collapsible',
                        legend: 'Ancient persons',
                        fields: [
                            this.createMultiSelect('Age', {model: 'attestation_age'}),
                            this.createMultiSelect('Education', {model: 'attestation_education'}),
                            this.createMultiSelect('Graph type', {model: 'attestation_graph_type'}),
                        ]
                    },
                    {
                        styleClasses: 'collapsible',
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
                'sortable': ['title'],
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
            let columns = ['id', 'tm_id', 'title']
            // if (this.commentSearch) {
            //     columns.unshift('comment')
            // }
            // if (this.isViewInternal) {
            //     columns.push('created');
            //     columns.push('modified');
            //     columns.push('actions');
            //     columns.push('c')
            // }
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
        formatPersonDate(date) {
            if (date == null || date.floor == null || date.ceiling == null) {
                return null
            }
            return date.floor + ' - ' + date.ceiling
        },
        formatInterval(born_floor, born_ceiling, death_floor, death_ceiling) {
            let born = born_floor === born_ceiling ? born_floor : born_floor + '-' + born_ceiling
            let death = death_floor === death_ceiling ? death_floor : death_floor + '-' + death_ceiling
            return born === death ? born : '(' + born + ') - (' + death + ')'
        },
        formatObjectArray(objects) {
            if (objects == null || objects.length === 0) {
                return null
            }
            return objects.map(objects => objects.name).join(', ')
        },

        greekSearch(searchQuery) {
            this.schema.fields.self_designation.values = this.schema.fields.self_designation.originalValues.filter(
                option => this.removeGreekAccents(option.name).includes(this.removeGreekAccents(searchQuery))
            );
        },
        collapse(e) {
            const group = e.target.parentElement;
            group.classList.toggle("collapsed");
        }
    },
    mounted() {
        const collapsableLegends = this.$el.querySelectorAll('.vue-form-generator .collapsible legend');
        collapsableLegends.forEach(legend => legend.onclick = this.collapse);
    }
}
</script>

<style lang="sass">
.vue-form-generator .collapsible {

    legend::after {
        content: '\f107';
        font-family: 'fontawesome';
        float: right;
        font-size: 100%;
        font-weight: normal;
        transition: 0.3s;
    }

    .form-group {
        transition: 0.3s;
    }

    &.collapsed {

        legend::after {
            transform: rotate(-90deg);
        }

        .form-group {
            display: block;
            height: 0;
            overflow: hidden;
            opacity: 0;
            padding: 0;
            margin: 0;
        }
    }
}
</style>