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
                        @model-updated="onAnnotationTypeUpdate"
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
                        class="form-group-sm"
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
                        <!-- TODO why is title an array when doing a search on title???-->
                        <a :href="getTextUrl(props.row.id, props.index)"
                           v-html="Array.isArray(props.row.title) ?
                           props.row.title[0] : props.row.title "/>
                    </template>
                    <template v-slot:id="props">
                        <a :href="getTextUrl(props.row.id, props.index)">
                            {{ props.row.id }}
                        </a>
                    </template>
                    <template v-slot:tm_id="props">
                        <a :href="getTextUrl(props.row.id, props.index)">
                            {{ props.row.tm_id }}
                        </a>
                    </template>
                    <template v-slot:annotations="props">
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
                            <span class="bg-tertiary small">Showing 3 of {{ props.row.annotations.length }} annotations.</span>
                        </div>
                    </template>
                    <template v-slot:instances_in_text="props">
                        <td>
                            {{ props.row.annotations_hits_count }}
                        </td>
                    </template>
                    <template v-slot:frequency_per_line="props">
                        <td>
                            {{ (props.row.annotations_hits_count / props.row.line_count).toFixed(2) }}
                        </td>
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

import AnnotationDetailsFlat from '../components/Annotations/AnnotationDetailsFlat'

import GreekText from '../components/Text/GreekText'

import PersistentConfig from "../components/Shared/PersistentConfig";
import SharedSearch from "../components/Search/SharedSearch";
import SearchAppFields from '../components/Search/Config'

import VtPerPageSelector from "vue-tables-2-premium/compiled/components/VtPerPageSelector";
import VtPagination from "vue-tables-2-premium/compiled/components/VtPagination";
import VtPaginationCount from "vue-tables-2-premium/compiled/components/VtPaginationCount";

Vue.component('fieldRadio', fieldRadio);

export default {
    components: {
        GreekText,
        AnnotationDetailsFlat,
        CheckboxSwitch,
        VtPerPageSelector,
        VtPagination,
        VtPaginationCount
    },
    mixins: [
        PersistentConfig('BaseAnnotationSearchConfig'),
        AbstractField,
        AbstractSearch,
        SharedSearch,
        SearchAppFields,
    ],
    props: {
        defaultAnnotationType: {
            type: String,
            default: null
        }
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
                title_combination: 'any',
                lines: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                columns: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                letters_per_line: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                width: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
                height: [AbstractField.RANGE_MIN_INVALID,AbstractField.RANGE_MAX_INVALID],
            },
            schema: {
                groups: [
                    this.annotationsFields(false, this.defaultAnnotationType),
                    this.generalInformationFields(),
                    this.materialityFields(true),
                    this.ancientPersonFields(true),
                    this.communicativeInformationFields(true),
                    this.handshiftFields(true),
                    this.genericStructureFieldsAnnotations(true),
                    this.administrativeInformationFields(true),
                ],
            },
            tableOptions: {
                filterByColumn: false,
                filterable: false,
                headings: {
                    id: 'ID',
                    tm_id: 'Tm ID ',
                    title: 'Title',
                    annotations: 'Annotations',
                    level_category: 'Text type'
                },
                columnsClasses: {
                    id: 'vue-tables__col vue-tables__col--id',
                    tm_id: 'vue-tables__col vue-tables__col--tm-id',
                    title: 'vue-tables__col vue-tables__col--title',
                    annotations: 'vue-tables__col vue-tables__col--annotations'
                },
                orderBy: {
                    'column': 'title'
                },
                perPage: 25,
                perPageValues: [25, 50, 100],
                sortable: ['id', 'tm_id', 'title', 'instances_in_text', 'frequency_per_line'],
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
            annotationFilter: null,
        }

        return data
    },
    computed: {
        tableColumns() {
            let columns = ['id', 'tm_id', 'title', 'annotations', 'instances_in_text', 'frequency_per_line', 'level_category', 'location_found']
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
        onAnnotationTypeUpdate(newVal, schema) {
            this.modelUpdated(newVal, schema);

            if ( schema === 'annotation_type' ) {
                const field_prefix = newVal.id

                this.schema.groups.filter( group => group?.id === 'annotations')[0].fields
                    .filter( field => field.model != 'annotation_type' )
                    .map( field => delete this.model[field.model] );
            }
        }

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
