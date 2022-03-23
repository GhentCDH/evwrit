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
                        legend: 'Generic text structure',
                        fields: [
                            // level
                            this.createMultiSelect('Level', { model: 'gts_textLevel' }),
                            this.createMultiSelect('Part', { model: 'gts_part' }),
                            this.createMultiSelect('Type', { model: 'gtsa_type' }),
                            this.createMultiSelect('Subtype', { model: 'gtsa_subtype' }),
                            this.createMultiSelect('Speech act', { model: 'gtsa_speechAct' }),
                            this.createMultiSelect('Information status', { model: 'gtsa_informationStatus' }),
                            this.createMultiSelect('Standard form', { model: 'gtsa_standardForm' }),
                            this.createMultiSelect('Attached to', { model: 'gtsa_attachedTo' }),
                            this.createMultiSelect('Type Attachment', { model: 'gtsa_attachmentType' }),

                        ]
                    },
                    // Generic text structure
                    {
                        styleClasses: 'collapsible collapsed',
                        legend: 'Layout text structure',
                        fields: [
                            this.createMultiSelect('Part', { model: 'lts_part' }),
                            this.createMultiSelect('Type', { model: 'ltsa_type' }),
                            this.createMultiSelect('Subtype', { model: 'ltsa_subtype' }),
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
            let columns = ['id', 'tm_id', 'title']
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
