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
                    :schema="schema"
                    :model="model"
                    :options="formOptions"
                    @model-updated="modelUpdated"
                    @validated="onValidated"
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
                :url="urls['text_search_api']"
                :columns="tableColumns"
                :options="tableOptions"
                @data="onData"
                @loaded="onLoaded"
            >
                <template
                    slot="h__self_designation"
                >
                    (Self) designation
                </template>
                <template
                    slot="name"
                    slot-scope="props"
                >
                    <a
                        v-if="props.row.name.constructor !== Array"
                        :href="urls['text_get'].replace('text_id', props.row.id)"
                    >
                        {{ props.row.name }}
                    </a>
                    <template v-else>
                        <!-- eslint-disable vue/no-v-html -->
                        <a
                            v-if="props.row.name.length === 1"
                            :href="urls['text_get'].replace('text_id', props.row.id)"
                            v-html="props.row.name[0]"
                        />
                        <!-- eslint-enable -->
                        <ul v-else>
                            <!-- eslint-disable vue/no-v-html -->
                            <li
                                v-for="(item, index) in props.row.name"
                                :key="index"
                                v-html="item"
                            />
                            <!-- eslint-enable -->
                        </ul>
                    </template>
                </template>
                <template
                    slot="c"
                    slot-scope="props"
                >
                    <span class="checkbox checkbox-primary">
                        <input
                            :id="props.row.id"
                            v-model="collectionArray"
                            :name="props.row.id"
                            :value="props.row.id"
                            type="checkbox"
                        >
                        <label :for="props.row.id" />
                    </span>
                </template>
            </v-server-table>
        </article>
        <div
            v-if="openRequests"
            class="loading-overlay"
        >
            <div class="spinner" />
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
        initPersons: {
            type: String,
            default: '',
        },
    },
    data() {
        let data = {
            model: {
                date_search_type: 'exact',
            },
            persons: null,
            schema: {
                fields: {
                    title: {
                        type: 'input',
                        inputType: 'text',
                        label: 'Title',
                        model: 'title',
                    },
                    id: {
                      type: 'input',
                      inputType: 'text',
                      label: 'Text ID',
                      model: 'id',
                    },
                    tm_id: {
                      type: 'input',
                      inputType: 'text',
                      label: 'Trismegistos ID',
                      model: 'tm_id',
                    },
                    year_from: {
                          type: 'input',
                          inputType: 'number',
                          label: 'Year from',
                          model: 'year_from',
                          min: AbstractSearch.YEAR_MIN,
                          max: AbstractSearch.YEAR_MAX,
                          validator: VueFormGenerator.validators.number,
                      },
                      year_to: {
                          type: 'input',
                          inputType: 'number',
                          label: 'Year to',
                          model: 'year_to',
                          min: AbstractSearch.YEAR_MIN,
                          max: AbstractSearch.YEAR_MAX,
                          validator: VueFormGenerator.validators.number,
                      },
                      date_search_type: {
                          type: 'radio',
                          label: 'The person date interval must ... the search date interval:',
                          labelClasses: 'control-label',
                          model: 'date_search_type',
                          values: [
                              { value: 'exact', name: 'exactly match' },
                              { value: 'included', name: 'be included in' },
                              { value: 'overlap', name: 'overlap with' },
                          ],
                      },
                      era: this.createMultiSelect('Era',
                          {
                            model: 'era'
                          },
                          {
                            multiple: true,
                            closeOnSelect: false,
                          }
                      ),
                      archive: this.createMultiSelect('Archive',
                          {
                            model: 'archive'
                          },
                          {
                            multiple: true,
                            closeOnSelect: false,
                          }
                      ),
                      project: this.createMultiSelect('Project',
                          {
                            model: 'project'
                          },
                          {
                            multiple: true,
                            closeOnSelect: false,
                          }
                      ),
                      script: this.createMultiSelect('Script',
                          {
                            model: 'script'
                          },
                          {
                            multiple: true,
                            closeOnSelect: false,
                          }
                      ),
                      material: this.createMultiSelect('Material',
                          {
                            model: 'material'
                          },
                          {
                            multiple: true,
                            closeOnSelect: false,
                          }
                      ),
                }
            },
            tableOptions: {
                headings: {
                    comment: 'Comment (matching lines only)',
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
                rowClassCallback: function(row) {
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
            data.schema.fields['historical'] = this.createMultiSelect(
                'Historical',
                {
                    styleClasses: 'has-warning',
                },
                {
                    customLabel: ({id, name}) => {
                        return name === 'true' ? 'Historical only' : 'Non-historical only'
                    },
                }
            )
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
    watch: {
    },
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
    }
}
</script>
