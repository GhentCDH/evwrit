<template>
    <section class="row search-app">
        <aside class="col-sm-3 search-app__filters scrollable">
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
                        @toggle-collapsed="onToggleCollapsed"
                />
            </div>
        </aside>
        <article class="col-sm-9 search-app__search-page">
            <header>
                <h1 v-if="title" class="mbottom-default">{{ title }}</h1>
                <div class="search-page__actions">
                    <div class="form-inline form-group">
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
                    </div>
                </div>
            </header>
            <section>
                <v-server-table
                        ref="resultTable"
                        :columns="tableColumns"
                        :options="tableOptions"
                        :url="getUrl('search_api')"
                        @data="onData"
                        @loaded="onLoaded"
                        class="form-group-sm "
                        perPageValues="5"
                >
                    <template v-slot:beforeTable>
                        <div class="VueTables__beforeTable row form-group form-inline">
                            <div class="VueTables__pagination col-xs-4">
                                <vt-pagination></vt-pagination>
                            </div>
                            <div class="VueTables__count col-xs-4">
                                <vt-pagination-count></vt-pagination-count>
                            </div>
                            <div class="VueTables__limit col-xs-4">
                                <vt-per-page-selector></vt-per-page-selector>
                            </div>
                        </div>
                    </template>

                    <template v-slot:title="props">
                        <!--TODO why is title an array when doing a search on title???-->
                        <a :href="getTextUrl(props.row.id, props.index)" @mouseup="handleLinkCLick"
                           v-html="Array.isArray(props.row.title) ?
                           props.row.title[0] : props.row.title "/>
                    </template>
                    <template v-slot:id="props">
                        <a :href="getTextUrl(props.row.id, props.index)" @mouseup="handleLinkCLick">
                            {{ props.row.id }}
                        </a>
                    </template>
                    <template v-slot:tm_id="props">
                        <a :href="getTextUrl(props.row.id, props.index)" @mouseup="handleLinkCLick">
                            {{ props.row.tm_id }}
                        </a>
                    </template>
                    <template v-slot:level_category="props">
                        <td>
                            {{ formatLevelCategory(props.row.level_category) }}
                        </td>
                    </template>
                    <template v-slot:location_found="props">
                        <td>
                            {{ props.row.location_found[0]?.name }}
                        </td>
                    </template>
                </v-server-table>
            </section>
        </article>
        <div
                v-if="openRequests"
                class="loading-overlay"
        >
            <div class="spinner"/>
        </div>
    </section>
</template>
<script>
import Vue from 'vue'
import VueFormGenerator from 'vue-form-generator'

import AbstractField from '../components/FormFields/AbstractField'
import AbstractSearch from '../components/Search/AbstractSearch'
import CheckboxSwitch from '../components/FormFields/CheckboxSwitch'

import fieldRadio from '../components/FormFields/fieldRadio'

import PersistentConfig from "../components/Shared/PersistentConfig";
import SharedSearch from "../components/Search/SharedSearch";
import SearchAppFields from '../components/Search/Config'

import VtPerPageSelector from "vue-tables-2-premium/compiled/components/VtPerPageSelector";
import VtPagination from "vue-tables-2-premium/compiled/components/VtPagination";
import VtPaginationCount from "vue-tables-2-premium/compiled/components/VtPaginationCount";

Vue.component('fieldRadio', fieldRadio);

export default {
    components: {
        CheckboxSwitch,
        VtPerPageSelector,
        VtPagination,
        VtPaginationCount
    },
    mixins: [
        PersistentConfig('MaterialitySearchConfig'),
        AbstractField,
        AbstractSearch,
        SharedSearch,
        SearchAppFields,
    ],
    props: {
    },
    data() {
        let data = {
            model: {
                date_search_type: 'exact',
                title_combination: 'any',
                lines: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                columns: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                letters_per_line: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                width: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                height: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
            },
            persons: null,
            schema: {
                groups: [
                    this.materialityFields(),
                    this.generalInformationFields(),
                    this.ancientPersonFields(true),
                    this.communicativeInformationFields(true),
                    this.administrativeInformationFields(true),
                ],
            },
            tableOptions: {
                filterByColumn: false,
                filterable: false,
                headings: {
                    level_category: 'Text type'
                },
                columnsClasses: {
                    name: 'no-wrap',
                },
                orderBy: {
                    'column': 'title'
                },
                perPage: 25,
                perPageValues: [25, 50, 100],
                sortable: ['id','tm_id','title', 'year_begin', 'year_end'],
                customFilters: ['filters'],
                requestFunction: AbstractSearch.requestFunction,
                rowClassCallback: function (row) {
                    return '';
                    // return (row.public == null || row.public) ? '' : 'warning'
                },
                pagination: {
                    show: false,
                    chunk: 5
                }
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
            let columns = ['id', 'tm_id', 'title', 'level_category', 'location_found','year_begin','year_end']
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
        formatLevelCategory(data) {
            // console.log(data)
            if (!data) return 'None';

            return data.map( item => item.level_category_category.name ).join(', ')
        },
    },
}
</script>