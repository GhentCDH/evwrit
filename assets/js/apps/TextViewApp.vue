<template>
    <div class="row">
        <CoolLightBox
                :items="images"
                :index="imageIndex"
                @close="imageIndex = null">
        </CoolLightBox>
        <article class="col-sm-9">
            <div class="scrollable scrollable--vertical">
                <div class="mbottom-small"> 
                    <h1 class="inline_title">{{ text.title }}</h1>
                    <h5 class="padding-20 inline_title">{{text.id ? 'EVWRIT ID:' + text.id : ''}} {{text.id && text.tm_id ? '—' : ''}} {{text.tm_id ? 'TM ID:' + text.tm_id : ''}}</h5>
                </div>
               
                <div class="row mbottom-large">

                    <!-- Text -->
                    <div v-if="config.text.show && !config.genericTextStructure.show && !config.layoutTextStructure.show" :class="textContainerClass" class="text">
                        <h2>Text</h2>
                        <GreekText :text="text.text" :annotations="visibleAnnotationsFormatted" :annotation-offset="1"/>
                    </div>

                    <!-- Lemmas -->
                    <div v-if="config.text.showLemmas && text.text_lemmas" :class="textContainerClass" class="text-lemmas">
                        <h2>Lemmas</h2>
                        <GreekText :text="text.text_lemmas"   />
                    </div>

                    <!-- Lemmas -->
                    <div v-if="config.text.showApparatus && text.apparatus" :class="textContainerClass" class="text-lemmas">
                        <h2>Apparatus</h2>
                        <GreekText :text="text.apparatus"   />
                    </div>

                    <!-- Generic Text Structure -->
                    <div v-if="config.genericTextStructure.show && genericTextStructure.length" :class="textContainerClass" class="text-structure">
                        <h2>Generic structure</h2>
                        <template v-if="config.genericTextStructure.groupByLevel">
                            <div class="level" v-for="level in genericTextStructureGroupedByLevel">
                                <label><span>Level {{ level.number }} {{ level.type}}</span></label>
                                <div class="structure" v-for="textStructure in level.children">
                                    <label><span>{{ textStructure.properties.gts_part.name }} {{ textStructure.properties.gts_part.part_number}}</span></label>
                                    <GreekText :text="textStructure.text_selection.text" :annotations="visibleAnnotationsFormatted" :annotation-offset="textStructure.text_selection.selection_start"></GreekText>
                                </div>
                            </div>
                        </template>
                        <template v-if="!config.genericTextStructure.groupByLevel">
                            <div class="structure" v-for="textStructure in genericTextStructure">
                                <label>
                                    <span v-if="textStructure.text_level">Level {{ textStructure.text_level.number }}</span>
                                    <span>{{ textStructure.properties.gts_part.name }} {{ textStructure.properties.gts_part.part_number}}</span>
                                </label>
                                <GreekText :text="textStructure.text_selection.text" :annotations="visibleAnnotationsFormattedNoLts" :annotation-offset="textStructure.text_selection.selection_start"></GreekText>
                            </div>
                        </template>
                    </div>

                    <!-- Layout Text Structure -->
                    <div v-if="config.layoutTextStructure.show && layoutTextStructure.length" :class="textContainerClass" class="text-structure">
                        <h2>Layout structure</h2>
                        <div class="structure" v-for="textStructure in layoutTextStructure">
                            <label>
                                <span>{{ textStructure.properties.lts_part.name }}</span>
                            </label>
                            <GreekText :text="textStructure.text_selection.text" :annotations="visibleAnnotationsFormattedNoGts" :annotation-offset="textStructure.text_selection.selection_start"></GreekText>
                        </div>
                    </div>

                    <!-- Translations -->
                    <div v-if="config.translation.show && text.translation.length" :class="textContainerClass" class="text-translations">
                        <div v-for="translation in text.translation" class="greek">
                            <h2>{{ translation.language.name}} Translation</h2>
                            <GreekText :text="translation.text"></GreekText>
                        </div>
                    </div>

                </div>

                <div class="row mbottom-large">

                    <!-- Annotations -->
                    <div v-if="config.annotations.showList" class="col-xs-12">
                        <h2 v-if="config.text.show || config.text.showLemmas || config.genericTextStructure.show">Annotations</h2>
                        <div class="annotation-result" v-for="annotation in visibleAnnotationsByContext">
                            <GreekText
                                    v-if="config.annotations.showContext && annotationHasContext(annotation)"
                                    :text="annotation.context.text"
                                    :annotations="formatAnnotation(annotation)"
                                    :annotationOffset="annotation.context.start + 1"
                                    :compact="true">
                            </GreekText>
                            <GreekText
                                    v-if="!config.annotations.showContext || !annotationHasContext(annotation)"
                                    :text="annotation.text_selection.text">
                            </GreekText>
                            <AnnotationDetailsFlat v-show="config.annotations.showDetails" :annotation="annotation"></AnnotationDetailsFlat>
                        </div>
                    </div>
                </div>
            </div>

        </article>
        <aside class="col-sm-3 scrollable scrollable--vertical">
            <div class="padding-default">

                <Widget v-if="isValidResultSet()" title="Search" :isOpen="true">
                    <div class="row mbottom-default">
                        <div class="col col-xs-3" :class="{ disabled: context.searchIndex === 1}">
                            <span class="btn btn-sm btn-primary" @click="loadTextByIndex(1)">&laquo;</span>
                            <span class="btn btn-sm btn-primary" @click="loadTextByIndex(context.searchIndex - 1)">&lt;</span>
                        </div>
                        <div class="col col-xs-6 text-center"> <input :placeholder="context.searchIndex" type="number" class="form-control input-sm input-no-controls" v-model="indexNumberInputValue" @keydown.enter="loadTextByIndex(indexNumberInputValue)"/> <span> of {{ resultSet.count }}</span></div>
                        <div class="col col-xs-3 text-right" :class="{ disabled: context.searchIndex === context.count}">
                            <span class="btn btn-sm btn-primary" @click="loadTextByIndex(context.searchIndex + 1)">&gt;</span>
                            <span class="btn btn-sm btn-primary" @click="loadTextByIndex( resultSet.count )">&raquo;</span>
                        </div>
                    </div>
                    <div v-if="hasSearchContext" class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showOnlyInSearchContext" class="switch-primary" label="Limit annotations to search context"></CheckboxSwitch>
                    </div>
                </Widget>



                <Widget title="Selection details" v-if="annotationId" :isOpen.sync="config.widgets.selectionDetails.isOpen">
                    <AnnotationDetails :annotation="annotationByTypeId[annotationId]"></AnnotationDetails>
                </Widget>

                <Widget title="Metadata" :is-open.sync="config.widgets.metadata.isOpen">
                    <LabelValue label="EVWRIT ID" :value="text.id"></LabelValue>
                    <LabelValue label="Trismegistos ID" :value="text.tm_id" :url="getTmTextUrl"></LabelValue>

                    <PropertyGroup>
                        <LabelValue label="Type" :value="text.text_type" type="id_name"></LabelValue>
                        <LabelValue label="Subtype" :value="text.text_subtype" type="id_name"></LabelValue>
                    </PropertyGroup>

                    <PropertyGroup>
                        <LabelValue label="Date" :value="{start: text.year_begin, end: text.year_end}" type="range"></LabelValue>
                        <LabelValue label="Era" :value="text.era" type="id_name" :url="urlGeneratorIdName('text_search', 'era')"></LabelValue>
                    </PropertyGroup>

                    <PropertyGroup>
                        <LabelValue label="Location written" :value="text.location_written" type="id_name"></LabelValue>
                        <LabelValue label="Location found" :value="text.location_found" type="id_name"></LabelValue>
                    </PropertyGroup>

                    <LabelValue v-if="text.keyword" label="Keywords" :value="text.keyword" :url="urlGeneratorIdName('text_search', 'keyword')" type="id_name"></LabelValue>

                </Widget>

                <Widget title="Images" :count="text.image.length" :is-open.sync="config.widgets.images.isOpen">
                    <Gallery :images="images" :onClick="(index,url) => (imageIndex = index)" />
                </Widget>

                <Widget title="Translations" :count="text.translation.length"  :is-open.sync="config.widgets.translations.isOpen">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.translation.show" class="switch-primary" label="Show translation(s)" :disabled="text.translation.length === 0"></CheckboxSwitch>
                    </div>
                </Widget>

                <Widget title="Text options" :is-open.sync="config.widgets.textOptions.isOpen">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.text.show" class="switch-primary" label="Show Text"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.text.showLemmas" class="switch-primary" label="Show Lemmas" :disabled="text.text_lemmas === ''"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.text.showApparatus" class="switch-primary" label="Show Apparatus" :disabled="text.apparatus === ''"></CheckboxSwitch>
                    </div>
                </Widget>

                <Widget title="Materiality" :is-open.sync="config.widgets.materiality.isOpen">
                    <PropertyGroup>
                        <LabelValue type="id_name" label="Production stage" :value="text.production_stage" :url="urlGeneratorIdName('materiality_search','production_stage')"></LabelValue>
                        <LabelValue type="id_name" label="Material" :value="text.material" :url="urlGeneratorIdName('materiality_search','material')"></LabelValue>
                        <LabelValue type="id_name" label="Writing direction" :value="text.writing_direction" :url="urlGeneratorIdName('materiality_search','writing_direction')"></LabelValue>
                        <LabelValue type="id_name" label="Format" :value="text.text_format" :url="urlGeneratorIdName('materiality_search','text_format')"></LabelValue>
                    </PropertyGroup>
                    <PropertyGroup>
                        <PageMetrics v-bind="text"></PageMetrics>
                    </PropertyGroup>
                    <PropertyGroup>
                        <LabelValue label="Lines" :value="arrayToRange(text.lines)"  type="range"></LabelValue>
                        <LabelValue label="Columns" :value="arrayToRange(text.columns)"  type="range"></LabelValue>
                        <LabelValue label="Letters per line" :value="arrayToRange(text.letters_per_line)" type="range"></LabelValue>
                        <LabelValue label="Interlinear space" :value="text.interlinear_space" ></LabelValue>
                    </PropertyGroup>
                </Widget>

                <Widget title="Attestation"  :is-open.sync="config.widgets.attestation.isOpen" :count="text.ancient_person.length">
                    <template v-for="person in text.ancient_person">
                        <h3>{{ person.name }}</h3>
                        <LabelValue label="Trismegistos ID" :value="person.tm_id" :url="getTmPersonUrl"></LabelValue>
                        <LabelValue label="Role" :value="person.role"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
                        <LabelValue label="Age" :value="person.age"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
                        <LabelValue label="Gender" :value="person.gender"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
                        <LabelValue label="Education" :value="person.education"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
                        <LabelValue label="Occupation" :value="person.occupation"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
                        <LabelValue label="Social Rank" :value="person.social_rank"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
                        <LabelValue label="Graph Type" :value="person.graph_type"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
                        <LabelValue label="Honorific Epithet" :value="person.honorific_epithet"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
                    </template>
                </Widget>

                <Widget title="Annotations" :is-open.sync="config.widgets.annotations.isOpen" :count="visibleAnnotationsByContext.length">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.show" class="switch-primary" label="Show annotations in text"></CheckboxSwitch>
                    </div>

                    <div class="form-group mtop-default">
                        <CheckboxSwitch v-model="config.annotations.showList" class="switch-primary" label="Show annotation list below text"></CheckboxSwitch>
                    </div>
                    <div v-if="config.annotations.showList" class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showContext" class="switch-primary" label="Show annotation in context"></CheckboxSwitch>
                    </div>
                    <div v-if="config.annotations.showList" class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showDetails" class="switch-primary" label="Show annotation details"></CheckboxSwitch>
                    </div>

                    <div v-if="showBaseAnnotations" class="mtop-default">
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.annotations.showTypography" class="switch-primary annotation-color-wrapper" label="Typography annotations">
                                <span class="count pull-right annotation-typography">{{ countAnnotationType('typography') }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.annotations.showLanguage" class="switch-primary annotation-color-wrapper" label="Language annotations">
                                <span class="count pull-right annotation-language">{{ countAnnotationType('language') }}</span>
                            </CheckboxSwitch>
                        </div>
                    </div>

                    <div v-if="showBaseAnnotations" class="mtop-default">
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.annotations.showMorphoSyntactical" class="switch-primary annotation-color-wrapper" label="Syntax annotations">
                                <span class="count pull-right annotation-morpho_syntactical">{{ countAnnotationType('morpho_syntactical') }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.annotations.showOrthography" class="switch-primary annotation-color-wrapper" label="Orthography annotations">
                                <span class="count pull-right annotation-orthography">{{ countAnnotationType('orthography') }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.annotations.showLexis" class="switch-primary annotation-color-wrapper" label="Lexis annotations">
                                <span class="count pull-right annotation-lexis">{{ countAnnotationType('lexis') }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.annotations.showMorphology" class="switch-primary annotation-color-wrapper" label="Morphology annotations">
                                <span class="count pull-right annotation-morphology">{{ countAnnotationType('morphology') }}</span>
                            </CheckboxSwitch>
                        </div>
                    </div>


                </Widget>

                <Widget title="Generic Structure" :is-open.sync="config.widgets.genericTextStructure.isOpen" :count="genericTextStructure.length">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.genericTextStructure.show" class="switch-primary" label="Show generic structure" :disabled="genericTextStructure.length === 0"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.genericTextStructure.groupByLevel" class="switch-primary" label="Reconstruct levels"  :disabled="genericTextStructure.length === 0"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.genericTextStructure.showAnnotations" class="switch-primary" label="Show generic structure annotations"></CheckboxSwitch>
                    </div>

                    <div v-if="showGTSA" class="mtop-default">
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.genericTextStructure.showUnit" class="switch-primary annotation-color-wrapper" label="Show Units">
                                <span class="count pull-right annotation-unit">{{ countGtsType('Unit') }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.genericTextStructure.showSubunit" class="switch-primary annotation-color-wrapper" label="Show Subunits">
                                <span class="count pull-right annotation-subunit">{{ countGtsType('Subunit') }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.genericTextStructure.showElement" class="switch-primary annotation-color-wrapper" label="Show Elements">
                                <span class="count pull-right annotation-element">{{ countGtsType('Element') }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.genericTextStructure.showModifier" class="switch-primary annotation-color-wrapper" label="Show Modifiers">
                                <span class="count pull-right annotation-modifier">{{ countGtsType('Modifier') }}</span>
                            </CheckboxSwitch>
                        </div>
                    </div>
                </Widget>

                <Widget title="Layout Structure" :is-open.sync="config.widgets.layoutTextStructure.isOpen" :count="layoutTextStructure.length">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.layoutTextStructure.show" class="switch-primary" label="Show layout structure" :disabled="layoutTextStructure.length === 0"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showHandshift" class="switch-primary" label="Show handwriting annotations"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.layoutTextStructure.showAnnotations" class="switch-primary" label="Show layout structure annotations"></CheckboxSwitch>
                    </div>

                    <div v-if="showLTSA" class="mtop-default">
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.layoutTextStructure.showUnit" class="switch-primary annotation-color-wrapper" label="Show Units">
                                <span class="count pull-right annotation-unit">{{ countLtsType('Unit') }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.layoutTextStructure.showSubunit" class="switch-primary annotation-color-wrapper" label="Show Subunits">
                                <span class="count pull-right annotation-subunit">{{ countLtsType('Subunit') }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.layoutTextStructure.showElement" class="switch-primary annotation-color-wrapper" label="Show Elements">
                                <span class="count pull-right annotation-element">{{ countLtsType('Element') }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.layoutTextStructure.showModifier" class="switch-primary annotation-color-wrapper" label="Show Modifiers">
                                <span class="count pull-right annotation-modifier">{{ countLtsType('Modifier') }}</span>
                            </CheckboxSwitch>
                        </div>
                    </div>
                </Widget>

                <Widget title="Links" :count="text.link.length" :is-open.sync="config.widgets.links.isOpen">
                    <div v-for="link in text.link">
                        <a :href="link.url">{{ link.title }}</a>
                    </div>
                </Widget>

            </div>
        </aside>
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
import Widget from '../components/Sidebar/Widget'
import LabelValue from '../components/Sidebar/LabelValue'
import PageMetrics from '../components/Sidebar/PageMetrics'
import GreekText from '../components/Text/GreekText'
import PropertyGroup from '../components/Sidebar/PropertyGroup'
import Gallery from '../components/Sidebar/Gallery'
import CheckboxSwitch from '../components/FormFields/CheckboxSwitch'
import AnnotationDetailsFlat from '../components/Annotations/AnnotationDetailsFlat'
import AnnotationDetails from '../components/Annotations/AnnotationDetails'

import PersistentConfig from "../components/Shared/PersistentConfig";
import ResultSet from "../components/Search/ResultSet";
import SearchSession from "../components/Search/SearchSession";
import SearchContext from "../components/Search/SearchContext";

import CoolLightBox from 'vue-cool-lightbox'
import 'vue-cool-lightbox/dist/vue-cool-lightbox.min.css'

import axios from 'axios'
import qs from 'qs'

export default {
    name: "TextViewApp",
    components: {
        Widget, LabelValue, PageMetrics, GreekText, CoolLightBox, PropertyGroup, Gallery, CheckboxSwitch, AnnotationDetailsFlat, AnnotationDetails
    },
    mixins: [
        PersistentConfig('TextViewConfig'),
        ResultSet,
        SearchSession,
        SearchContext,
    ],
    props: {
        initUrls: {
            type: String,
            required: true
        },
        initData: {
            type: String,
            required: true
        }
    },
    data() {
        let data = {
            urls: JSON.parse(this.initUrls),
            data: JSON.parse(this.initData),
            defaultConfig: {
                search: {
                    useContext: true,
                },
                text: {
                    show: true,
                    showLemmas: false,
                    showApparatus: false,
                },
                translation: {
                    show: true,
                },
                annotations: {
                    show: true,
                    showList: false,
                    showOnlyInSearchContext: true,
                    showDetails: true,
                    showContext: true,
                    showTypography: true,
                    showLanguage: true,
                    showOrthography: true,
                    showMorphology: true,
                    showLexis: true,
                    showMorphoSyntactical: true,
                    showHandshift: true,
                },
                genericTextStructure: {
                    show: false,
                    groupByLevel: false,
                    showAnnotations: false,
                    showUnit: true,
                    showSubunit: true,
                    showElement: true,
                    showModifier: true
                },
                layoutTextStructure: {
                    show: false,
                    showAnnotations: false,
                    showUnit: true,
                    showSubunit: true,
                    showElement: true,
                    showModifier: true
                },
                widgets: {
                    selectionDetails: { isOpen: false },
                    metadata: { isOpen: false },
                    translations: { isOpen: false },
                    textOptions: { isOpen: false },
                    materiality: { isOpen: false },
                    attestation: { isOpen: false },
                    annotations: { isOpen: false },
                    genericTextStructure: { isOpen: false },
                    layoutTextStructure: { isOpen: false },
                    images: { isOpen: false },
                    links: { isOpen: false },
                }
            },
            imageIndex: null,
            annotationId: null,
            openRequests: false,
            indexNumberInputValue: null,
        }
        return data
    },
    computed: {
        text: function() {
            return this.data.text
        },
        images: function() {
            let result = []
            if ( this.data.text.hasOwnProperty('image') && this.data.text.image.length ) {
                this.data.text.image.forEach( function(image,index) {
                    result.push({
                        description: image.copyright ? 'Copyright: ' + image.copyright : null,
                        src: 'https://media.evwrit.ugent.be/image.php?secret=RHRVvbV4ScZITUVjfbab85DCteR9dsPgw2s5G2aD&filename=' +image.filename
                    })
                })
            }
            return result
        },
        annotationByTypeId() {
            let result = []
            this.data.text.annotations.forEach( anno => result[anno.type+':'+anno.id] = anno )

            return result;
        },
        visibleAnnotationTypes() {
            let ret = [];
            if ( this.config.annotations.show ) {
                this.config.annotations.showLanguage && ret.push('language');
                this.config.annotations.showTypography && ret.push('typography');
                this.config.annotations.showOrthography && ret.push('orthography');
                this.config.annotations.showMorphology && ret.push('morphology');
                this.config.annotations.showLexis && ret.push('lexis');
                this.config.annotations.showMorphoSyntactical && ret.push('morpho_syntactical');
            }
            this.config.annotations.showHandshift && ret.push('handshift');
            this.config.genericTextStructure.showAnnotations && ret.push('gtsa');
            this.config.layoutTextStructure.showAnnotations && ret.push('ltsa');

            this.bindEvents();
            return ret;
        },
        visibleLTSATypes() {
            let ret = [];
            if ( this.showLTSA ) {
                this.config.layoutTextStructure.showUnit && ret.push('Unit');
                this.config.layoutTextStructure.showSubunit && ret.push('Subunit');
                this.config.layoutTextStructure.showModifier && ret.push('Modifier');
                this.config.layoutTextStructure.showElement && ret.push('Element');
            }
            this.bindEvents();
            return ret;
        },
        visibleGTSATypes() {
            let ret = [];
            if ( this.showGTSA ) {
                this.config.genericTextStructure.showUnit && ret.push('Unit');
                this.config.genericTextStructure.showSubunit && ret.push('Subunit');
                this.config.genericTextStructure.showModifier && ret.push('Modifier');
                this.config.genericTextStructure.showElement && ret.push('Element');
            }
            this.bindEvents();
            return ret;
        },
        visibleAnnotationsByContext() {
            let annotations = this.text.annotations

            // filter by search context?
            if ( this.config.annotations.showOnlyInSearchContext && (this.context.params ?? false) ) {
                annotations = this.annotationsFilterByContext(annotations, this.context.params ?? {})
            }

            return annotations
        },
        visibleAnnotations() {
            let annotations = this.visibleAnnotationsByContext

            // filter by config
            annotations = this.annotationsFilterByConfig(annotations)

            // sort & return
            return annotations.sort( function(annotation_1, annotation_2) {
                    return annotation_1.text_selection.selection_start - annotation_2.text_selection.selection_start
                });
        },
        visibleAnnotationsFormatted() {
            return this.visibleAnnotations.reduce( (result, annotation) => result.concat(this.formatAnnotation(annotation)), [] );
        },
        visibleAnnotationsFormattedNoGts() {
            return this.visibleAnnotations.reduce( (result, annotation) => result.concat(annotation.type != 'gtsa' ? this.formatAnnotation(annotation) : []), [] );
        },
        visibleAnnotationsFormattedNoLts() {
            return this.visibleAnnotations.reduce( (result, annotation) => result.concat(annotation.type != 'ltsa' ? this.formatAnnotation(annotation) : []), [] );
        },
        showBaseAnnotations() {
            return this.config.annotations.show || this.config.annotations.showList
        },
        showGTSA() {
            return this.config.genericTextStructure.showAnnotations
        },
        showLTSA() {
            return this.config.layoutTextStructure.showAnnotations
        },
        hasSearchContext() {
           return Object.keys(this.context.params ?? {} ).length > 0
        },
        // get generic text structure annotations
        genericTextStructure() {
            let ret = {}

            ret = this.data.text.annotations
                .filter( function(annotation) {
                    return annotation.type === 'gts'
                })
                .sort( (a,b) => a.text_selection.selection_start - b.text_selection.selection_start )

            return ret
        },
        // get layout text structure annotations
        layoutTextStructure() {
            let ret = {}

            ret = this.data.text.annotations
                .filter( function(annotation) {
                    return annotation.type === 'lts'
                })
                .sort( (a,b) => a.text_selection.selection_start - b.text_selection.selection_start )

            return ret
        },
        // group text structure annotations by level number
        // catch: not all annotations have a level assigned
        genericTextStructureGroupedByLevel() {
            let ret = {}

            this.genericTextStructure.forEach( function(annotation) {
                    let level_number =  String(annotation?.properties?.gts_textLevel?.number || 0);
                    let level_properties = annotation?.properties?.gts_textLevel?.number ? annotation?.properties?.gts_textLevel : { number: 0, type: "" }
                    if (!(level_number in ret)) {
                        ret[level_number] = { ...level_properties, ...{ children: [] } }
                    }
                    ret[level_number].children.push(annotation)
                });
            return ret;
        },
        textContainersOpen() {
            let conf = [
                this.config.text.show ? 1 : 0,
                this.config.text.showLemmas && this.text.text_lemmas ? 1 : 0,
                this.config.text.showApparatus && this.text.apparatus ? 1 : 0,
                this.config.translation.show && this.text.translation.length ? 1 : 0,
                this.config.genericTextStructure.show && this.genericTextStructure.length ? 1 : 0,
                this.config.layoutTextStructure.show && this.layoutTextStructure.length ? 1 : 0
            ]
            return conf.reduce((partial_sum, a) => partial_sum + a, 0);
        },
        textContainerClass() {
            let strClass = '';
            switch(this.textContainersOpen) {
                case 1:
                    strClass = 'col-xs-12';
                    break;
                case 2:
                    strClass = 'col-xs-12 col-md-6';
                    break;
                default:
                    strClass = 'col-xs-12 col-lg-4 col-md-6';
                    break;
            }
            return strClass;
        }
    },
    methods: {
        annotationHasContext(annotation) {
           return !!annotation?.context
        },
        annotationsFilterByConfig(annotations) {
            let that = this
            return annotations
                // filter by annotation type
                .filter( function(annotation) {
                    return that.visibleAnnotationTypes.includes(annotation.type)
                })
                // filter gtsa annotations by gtsa_type
                .filter( function(annotation) {
                    return annotation.type !== "gtsa" || ( annotation.type === "gtsa" && that.visibleGTSATypes.includes(annotation.properties?.gtsa_type?.name) )
                })
                // filter ltsa annotations by ltsa_type
                .filter( function(annotation) {
                    return annotation.type !== "ltsa" || ( annotation.type === "ltsa" && that.visibleLTSATypes.includes(annotation.properties?.ltsa_type?.name) )
                })
        },
        annotationsFilterByContext(annotations, context_params) {
            let annotationTypeFilter = context_params?.annotation_type?.value ?? false

            let annotationPropertyPrefixes = ['language', 'typography', 'orthography', 'lexis', 'morpho_syntactical','handshift','ltsa','gtsa']
            let annotationPropertyFilters = {}
            for ( const [key, param] of Object.entries(context_params) ) {
                for ( const prefix of annotationPropertyPrefixes ) {
                    if (key.startsWith(prefix + '_') ) {
                        annotationPropertyFilters[key] = Array.isArray(param.value) ? param.value : [ param.value ]
                    }
                }
            }

            return annotations.filter( function(annotation) {
                // filter by type
                if ( annotationTypeFilter ) {
                    if ( Array.isArray(annotationTypeFilter) && !annotationTypeFilter.includes(annotation.type) )
                        return false
                    else if ( !Array.isArray(annotationTypeFilter) && annotationTypeFilter !== annotation.type )
                        return false
                }

                // filter by property
                for ( const [filterKey, filterValues] of Object.entries(annotationPropertyFilters) ) {
                    // check if annotation has this property
                    if ( !annotation.properties.hasOwnProperty(filterKey) ) {
                        return false
                    }

                    // check if property has value
                    if ( !annotation.properties[filterKey] ) {
                        return false
                    }

                    // check if property matches includes at least one of the filter values
                    let propertyValues = Array.isArray(annotation.properties[filterKey]) ? annotation.properties[filterKey] : [ annotation.properties[filterKey] ];
                    let valuesMatched = propertyValues.filter( function(propertyValue) {
                        return filterValues.includes(propertyValue.id)
                    })
                    if ( valuesMatched.length === 0 ) {
                        return false
                    }
                }

                return true
            } )
        },
        formatAnnotation(annotation) {
            switch ( annotation.text_selection.selection_start - (annotation.text_selection.selection_end -1) ) {
                case 1:
                    return [
                        [
                            annotation.text_selection.selection_start,
                            annotation.text_selection.selection_end -1,
                            { data: { id: annotation.type + ':' + annotation.id }, class: this.getAnnotationClass(annotation) }
                        ]
                    ]
                case 2:
                    return [
                        [
                            annotation.text_selection.selection_start,
                            annotation.text_selection.selection_start,
                            { data: { id: annotation.type + ':' + annotation.id }, class: this.getAnnotationClass(annotation) }
                        ],
                        [
                            annotation.text_selection.selection_start + 1,
                            annotation.text_selection.selection_end -1,
                            { data: { id: annotation.type + ':' + annotation.id }, class: this.getAnnotationClass(annotation) }
                        ]
                    ]
                default:
                    return [
                        [
                            annotation.text_selection.selection_start,
                            annotation.text_selection.selection_start,
                            { data: { id: annotation.type + ':' + annotation.id }, class: this.getAnnotationClass(annotation, 'annotation-start') }
                        ],
                        [
                            annotation.text_selection.selection_start + 1,
                            annotation.text_selection.selection_end - 2,
                            { data: { id: annotation.type + ':' + annotation.id }, class: this.getAnnotationClass(annotation) }
                        ],
                        [
                            annotation.text_selection.selection_end - 1,
                            annotation.text_selection.selection_end - 1,
                            { data: { id: annotation.type + ':' + annotation.id }, class: this.getAnnotationClass(annotation, 'annotation-end') }
                        ]
                    ]
            }
        },
        getAnnotationClass(annotation, extra) {
            let classes = [];
            switch(annotation.type) {
                case 'gtsa':
                    if ( annotation.properties?.gtsa_type?.name ) {
                        classes = classes.concat( ['annotation-' + annotation.type + '-' + annotation.properties.gtsa_type.name.toLowerCase()] );
                    }
                case 'ltsa':
                    if ( annotation.properties?.ltsa_type?.name ) {
                        classes = classes.concat( ['annotation-' + annotation.type + '-' + annotation.properties.ltsa_type.name.toLowerCase()] );
                    }
                case 'handshift':
                    if ( annotation.internal_hand_num && annotation.internal_hand_num.match(/(\d+)/) ) {
                        classes = classes.concat( ['annotation-handshift-' + annotation.internal_hand_num.match(/(\d+)/)[0]] );
                    }
                default:
                    classes = classes.concat(['annotation', 'annotation-' + annotation.type, 'annotation-' + annotation.type + '-' + annotation.id]);
            }
            if ( extra ) {
                classes.push(extra)
            }
            return classes.join(' ');
        },
        countAnnotationType(type) {
            return this.visibleAnnotationsByContext.filter( item => item.type === type ).length;
        },
        countGtsType(type) {
            return this.visibleAnnotationsByContext.filter( item => item.properties?.gtsa_type?.name === type ).length;
        },
        countLtsType(type) {
            return this.visibleAnnotationsByContext.filter ( item => item.properties?.ltsa_type?.name === type ).length;
        },
        urlGeneratorIdName(url, filter) {
            return (value) => ( this.getUrl(url) + '?' + qs.stringify( { filters: {[filter]: value.id } } ) )
        },
        getUrl(route) {
            return this.urls[route] ?? ''
        },
        getTmTextUrl(id) {
            return 'https://www.trismegistos.org/text/' + id
        },
        getTmPersonUrl(id) {
            return 'https://www.trismegistos.org/person/' + id
        },
        getTextUrl(id) {
            let url = this.urls['text_get_single'].replace('text_id', id);
            if (this.isValidContext()) {
                url += '#' + this.getContextHash()
            }
            return url
        },
        loadText(id) {
            this.openRequests += 1
            let url = this.getUrl('text_get_single').replace('text_id',id)
            return axios.get(url).then( (response) => {
                if (response.data) {
                    this.data.text = response.data;
                }
                this.openRequests -= 1
            })
        },
        clickAnnotation(e) {
            e.stopPropagation()

            // get annotation id
            let typeId = e.target?.dataset?.id;
            if ( typeId ) {
                this.annotationId = typeId
                // open selection details widget
                this.config.widgets.selectionDetails.isOpen = true
            }
        },
        bindEvents() {
            this.$nextTick(function () {
                const annotations = this.$el.querySelectorAll('.annotation')
                annotations.forEach(annotation => annotation.addEventListener('click', this.clickAnnotation) )
            })
        },
        arrayToRange(value) {
            if ( value ) {
                return {start: value[0], end: value[1]}
            }
            return null;
        },

        loadTextByIndex(index) {
            let that = this;
            if ( !this.resultSet.count ) return;

            let newIndex = Math.max(1, Math.min(index, this.resultSet.count))
            this.getResultSetIdByIndex(newIndex).then( function(id) {
                that.loadText(id).then((response) => {
                    // update context
                    that.context.searchIndex = newIndex
                    // update state
                    window.history.replaceState({}, '', that.getTextUrl(id));
                    // bind events
                    that.bindEvents();
                    // update input field value
                    that.indexNumberInputValue = newIndex;
                });
            })
        },
        isValidResultSet() {
            return this.context?.searchIndex && this.resultSet?.count
        }

    },
    created() {
        // make annotations clickable
        this.bindEvents();

        // update annotation events on config change
        this.$on('config-changed', function(config) {
            this.bindEvents();
        })

        // init context
        this.initContextFromUrl()

        // init ResultSet based on SearchSession
        if ( this.context?.searchSessionHash ) {
            let searchSession = this.getSearchSession(this.context.searchSessionHash)
            if ( searchSession ) {
                this.initResultSet(searchSession.urls.paginate, searchSession.params, searchSession.count)
            }
        }
    },
}
</script>

<style scoped lang="scss">
#text-view-app {
  display: flex;
  flex-direction: row;
  flex: 1;
  overflow: hidden;
  height: 100%;

  article {
    display: flex;

    & > div {
      width: 100%;
    }
  }

  .input-no-controls {
    display: inline;
    width: 3.5em;
    margin-right: 0.5em;
  }

  .inline_title {
    display: inline;
  }

  aside {
    background-color: #fafafa !important;


    .widget {
      border-bottom: 1px solid #e9ecef;
    }
  }
}

</style>