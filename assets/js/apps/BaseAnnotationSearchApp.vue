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
                                    <CheckboxSwitch v-model="config.showAnnotationContext" class="switch-primary" label="Show annotation context"></CheckboxSwitch>
                                </div>
                            </li>
                            <li>
                                <div class="form-group">
                                    <CheckboxSwitch v-model="config.showAnnotationDetails" class="switch-primary" label="Show annotation details"></CheckboxSwitch>
                                </div>
                            </li>
                            <li>
                                <div class="form-group">
                                    <CheckboxSwitch v-model="config.showAnnotationTypeOnlyProperties" class="switch-primary" label="Limit visible annotation properties to own type"></CheckboxSwitch>
                                </div>
                            </li>
                            <li>
                                <div class="form-group">
                                    <CheckboxSwitch v-model="config.limitVisibleAnnotations" class="switch-primary" label="Show maximum 3 annotations per text"></CheckboxSwitch>
                                </div>
                            </li>
                            <li role="separator" class="divider"></li>
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
                <template slot="annotations" slot-scope="props">
                    <div class="annotation-result" v-for="annotation in limitAnnotations(props.row.annotations)">
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
                        <AnnotationDetailsFlat v-show="config.showAnnotationDetails" :annotation="annotation" :type-only-properties="config.showAnnotationTypeOnlyProperties"></AnnotationDetailsFlat>
                    </div>
                    <div class="annotation-count" v-if="config.limitVisibleAnnotations && props.row.annotations.length > 3">
                        <span class="bg-tertiary small">Showing 3 of {{ props.row.annotations.length }} annotations found in text.</span>
                    </div>
                </template>
                <template slot="level_category" slot-scope="props">
                    <td>
                        {{ formatLevelCategory(props.row.level_category) }}
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

import SearchAppFields from '../components/Search/Config'
import AbstractField from '../components/FormFields/AbstractField'
import AbstractSearch from '../components/Search/AbstractSearch'
import CheckboxSwitch from '../components/FormFields/CheckboxSwitch'


import AnnotationDetailsFlat from '../components/Annotations/AnnotationDetailsFlat'

import fieldRadio from '../components/FormFields/fieldRadio'
import GreekText from '../components/Text/GreekText'

import CollapsibleGroups from '../components/Search/CollapsibleGroups'
import ExpertGroups from '../components/Search/ExpertGroupsAnnotations'
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
        PersistentConfig('BaseAnnotationSearchConfig'),
        AbstractField,
        AbstractSearch,
        SharedSearch,
        SearchAppFields,
    ],
    props: {
    },
    data() {
        let data = {
            defaultConfig: {
                showAnnotationContext: true,
                showAnnotationDetails: false,
                showAnnotationTypeOnlyProperties: true,
                limitVisibleAnnotations: true,
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
                    this.annotationsFields(),
                    this.genericStructureFieldsAnnotations(),
                    this.handshiftFields(),
                    this.generalInformationFields(),
                    this.communicativeInformationFields(),
                    this.materialityFields(),
                    this.ancientPersonFields(),
                    this.administrativeInformationFields(),
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
            let columns = ['id', 'tm_id', 'title', 'annotations', 'level_category', 'location_found']
            return columns
        },
    },
    watch: {
        defaultOrdering: function(val) {
        },
    },
    methods: {
        update() {
            // Don't create a new history item
            this.noHistory = true;
            this.$refs.resultTable.refresh();
        },
        limitAnnotations(annotations) {
            return this.config.limitVisibleAnnotations ? annotations.slice(0,3) : annotations
        },
        formatLevelCategory(data) {
            if (!data) return 'None';

            return data.map( item => item.level_category_category.name ).join(', ')
        },

    },
}
</script>

<style lang="scss">
.annotation-result {
  border: 0;
  border-top: 1px solid #ccc;
}
.annotation-result:first-child {
  border: 0;
}
</style>
