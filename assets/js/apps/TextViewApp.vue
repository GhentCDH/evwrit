<template>
    <div>
        <CoolLightBox
                :items="images"
                :index="imageIndex"
                @close="imageIndex = null">
        </CoolLightBox>
        <article class="col-sm-9">
            <h1>{{ text.title }}</h1>

            <!-- result navigation -->
<!--            <div class="hidden">-->
<!--                <b-button class="btn" @click="gotoNextText()">prev</b-button>-->
<!--                <b-button class="btn" href="">back to search</b-button>-->
<!--                <b-button class="btn" @click="gotoPrevText()">next</b-button>-->
<!--            </div>-->

            <div class="row mbottom-large">

                <!-- Text -->
                <div v-if="config.text.show" :class="textContainerClass" class="text">
                    <h2>Text</h2>
                    <GreekText :text="text.text" :annotations="visibleAnnotationsFormatted" :annotation-offset="1"/>
                </div>

                <!-- Lemmas -->
                <div v-if="config.text.showLemmas" :class="textContainerClass" class="text-lemmas">
                    <h2>Lemmas</h2>
                    <GreekText :text="text.text_lemmas"   />
                </div>

                <!-- Lemmas -->
                <div v-if="config.text.showApparatus" :class="textContainerClass" class="text-lemmas">
                    <h2>Apparatus</h2>
                    <GreekText :text="text.apparatus"   />
                </div>

                <!-- Translations -->
                <div v-if="config.translation.show" :class="textContainerClass" class="text-translations">
                    <div v-for="translation in text.translation" class="greek">
                        <h2>{{ translation.language.name}} Translation</h2>
                        <GreekText :text="translation.text"></GreekText>
                    </div>
                </div>

                <!-- Generic Text Structure -->
                <div v-if="config.genericTextStructure.show" :class="textContainerClass" class="text-structure">
                    <h2>Generic text structure</h2>
                    <template v-if="config.genericTextStructure.groupByLevel">
                        <div class="level" v-for="level in genericTextStructureGroupedByLevel">
                            <label><span>Level {{ level.number }} {{ level.type}}</span></label>
                            <div class="structure" v-for="textStructure in level.children">
                                <label><span>{{ textStructure.part.name }} {{ textStructure.part_number}}</span></label>
                                <GreekText :text="textStructure.text_selection.text" :annotations="visibleAnnotationsFormatted" :annotation-offset="textStructure.text_selection.selection_start"></GreekText>
                            </div>
                        </div>
                    </template>
                    <template v-if="!config.genericTextStructure.groupByLevel">
                        <div class="structure" v-for="textStructure in genericTextStructure">
                            <label>
                                <span v-if="textStructure.text_level">Level {{ textStructure.text_level.number }}</span>
                                <span>{{ textStructure.part.name }} {{ textStructure.part_number}}</span>
                            </label>
                            <GreekText :text="textStructure.text_selection.text" :annotations="visibleAnnotationsFormatted" :annotation-offset="textStructure.text_selection.selection_start"></GreekText>
                        </div>
                    </template>
                </div>

            </div>

            <div class="row mbottom-large">

                <!-- Annotations -->
                <div v-if="config.annotations.showList" class="col-xs-12">
                    <h2 v-if="config.text.show || config.text.showLemmas || config.genericTextStructure.show">Annotations</h2>
                    <div class="annotation-result" v-for="annotation in visibleAnnotations">
                        <GreekText
                                v-show="config.annotations.showContext"
                                :text="annotation.context.text"
                                :annotations="[ formatAnnotation(annotation) ]"
                                :annotationOffset="annotation.context.start + 1"
                                :compact="true">
                        </GreekText>
                        <GreekText
                                v-show="!config.annotations.showContext"
                                :text="annotation.text_selection.text">
                        </GreekText>
                        <AnnotationDetailsFlat v-show="config.annotations.showDetails" :annotation="annotation"></AnnotationDetailsFlat>
                    </div>
                </div>
            </div>
        </article>
        <aside class="col-sm-3">
            <div class="padding-default">

                <div :if="context.count > 1">
                    <ul class="pager">
                        <li class="previous" :class="{ disabled: context.index === 1}"><a @click="loadPrevText()"><span aria-hidden="true">&larr;</span> Previous</a></li>
                        <li class=""><span>Result {{ context.index }} of {{ context.count }}</span></li>
                        <li class="next" :class="{ disabled: context.index === context.count}"><a @click="loadNextText()">Next <span aria-hidden="true">&rarr;</span></a></li>
                    </ul>
                </div>

                <Widget title="Selection details" v-if="annotationId">
                    <AnnotationDetails :annotation="annotationByTypeId[annotationId]"></AnnotationDetails>
                </Widget>

                <Widget title="Metadata">
                    <LabelValue label="ID" :value="text.id"></LabelValue>
                    <LabelValue label="Trismegistos ID" :value="text.tm_id" :url="urlTmId"></LabelValue>

                    <PropertyGroup>
                        <LabelValue label="Type" :value="text.text_type" type="id_name"></LabelValue>
                        <LabelValue label="Subtype" :value="text.text_subtype" type="id_name"></LabelValue>
                    </PropertyGroup>

                    <PropertyGroup>
                        <LabelValue label="Date" :value="{start: text.year_begin, end: text.year_end}" type="range"></LabelValue>
                        <LabelValue label="Era" :value="text.era" type="id_name" :url="urlGeneratorIdName('text_search', 'era')"></LabelValue>
                    </PropertyGroup>


                    <LabelValue v-if="text.keyword" label="Keywords" :value="text.keyword" :url="urlGeneratorIdName('text_search', 'keyword')" type="id_name"></LabelValue>


                </Widget>

                <Widget title="Translations" :count="text.translation.length" :init-open="false">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.translation.show" class="switch-primary" label="Show translation(s)"></CheckboxSwitch>
                    </div>
                </Widget>

                <Widget title="Text options">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.text.show" class="switch-primary" label="Show Text"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.text.showLemmas" class="switch-primary" label="Show Lemmas"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.text.showApparatus" class="switch-primary" label="Show Apparatus"></CheckboxSwitch>
                    </div>
                </Widget>

                <Widget title="Materiality" :init-open="false">
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

                <Widget title="Attestation" :init-open="false" :count="text.ancient_person.length">
                    <template v-for="person in text.ancient_person">
                        <h3>{{ person.name }}</h3>
                        <LabelValue label="Role" :value="person.role"  type="id_name"></LabelValue>
                        <LabelValue label="Age" :value="person.age"  type="id_name"></LabelValue>
                        <LabelValue label="Gender" :value="person.gender"  type="id_name"></LabelValue>
                        <LabelValue label="Education" :value="person.education"  type="id_name"></LabelValue>
                        <LabelValue label="Occupation" :value="person.occupation"  type="id_name"></LabelValue>
                        <LabelValue label="Social Rank" :value="person.social_rank"  type="id_name"></LabelValue>
                        <LabelValue label="Graph Type" :value="person.graph_type"  type="id_name"></LabelValue>
                        <LabelValue label="Honorific Epithet" :value="person.honorific_epithet"  type="id_name"></LabelValue>
                    </template>
                </Widget>

                <Widget title="Annotations" :init-open="false" :count="visibleAnnotationsByContext.length">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.show" class="switch-primary" label="Show annotations in text"></CheckboxSwitch>
                    </div>

                    <div v-if="showAnnotationOptions && hasSearchContext" class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showOnlyInSearchContext" class="switch-primary" label="Limit annotations to search context"></CheckboxSwitch>
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

                    <div v-if="showAnnotationOptions" class="form-group mtop-default">
                        <CheckboxSwitch v-model="config.annotations.showLanguage" class="switch-primary" label="Language annotations">
                            <span class="count pull-right annotation-language">{{ countAnnotationType('language') }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div v-if="showAnnotationOptions" class="form-group mbottom-default">
                        <CheckboxSwitch v-model="config.annotations.showTypography" class="switch-primary" label="Typography annotations">
                            <span class="count pull-right annotation-typography">{{ countAnnotationType('typography') }}</span>
                        </CheckboxSwitch>
                    </div>

                    <div v-if="showAnnotationOptions" class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showMorphoSyntactical" class="switch-primary" label="Morpho-Syntactical annotations">
                            <span class="count pull-right annotation-morpho_syntactical">{{ countAnnotationType('morpho_syntactical') }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div v-if="showAnnotationOptions"  class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showOrthography" class="switch-primary" label="Orthography annotations">
                            <span class="count pull-right annotation-orthography">{{ countAnnotationType('orthography') }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div v-if="showAnnotationOptions" class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showLexis" class="switch-primary" label="Lexis annotations">
                            <span class="count pull-right annotation-lexis">{{ countAnnotationType('lexis') }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div v-if="showAnnotationOptions" class="form-group mbottom-default">
                        <CheckboxSwitch v-model="config.annotations.showMorphology" class="switch-primary" label="Morphology annotations">
                            <span class="count pull-right annotation-morphology">{{ countAnnotationType('morphology') }}</span>
                        </CheckboxSwitch>
                    </div>

                </Widget>

                <Widget title="Generic Text Structure" :init-open="false" :count="text.annotations.length">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.genericTextStructure.show" class="switch-primary" label="Show generic text structure"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.genericTextStructure.groupByLevel" class="switch-primary" label="Reconstruct levels"></CheckboxSwitch>
                    </div>
                </Widget>

                <Widget title="Images" :count="text.image.length" :init-open="false">
                    <Gallery :images="images" :onClick="(index,url) => (imageIndex = index)" />
                </Widget>

                <Widget title="Links" :count="text.link.length" :init-open="false">
                    <div v-for="link in text.link">
                        <a :href="link.url">{{ link.title }}</a>
                    </div>
                </Widget>

            </div>
        </aside>
    </div>
</template>

<script>
import Vue from 'vue'
import Widget from '../Components/Sidebar/Widget'
import LabelValue from '../Components/Sidebar/LabelValue'
import PageMetrics from '../Components/Sidebar/PageMetrics'
import GreekText from '../Components/Shared/GreekText'
import PropertyGroup from '../Components/Sidebar/PropertyGroup'
import Gallery from '../Components/Sidebar/Gallery'
import CheckboxSwitch from '../Components/Shared/CheckboxSwitch'
import AnnotationDetailsFlat from '../Components/Annotations/AnnotationDetailsFlat'
import AnnotationDetails from '../Components/Annotations/AnnotationDetails'

import PersistentConfig from "../Components/Shared/PersistentConfig";

import CoolLightBox from 'vue-cool-lightbox'
import 'vue-cool-lightbox/dist/vue-cool-lightbox.min.css'

import axios from 'axios'
import qs from 'qs'
import _merge from "lodash.merge";

export default {
    name: "TextViewApp",
    components: {
        Widget, LabelValue, PageMetrics, GreekText, CoolLightBox, PropertyGroup, Gallery, CheckboxSwitch, AnnotationDetailsFlat, AnnotationDetails
    },
    mixins: [PersistentConfig('TextViewConfig')],
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
                    showMorphoSyntactical: true
                },
                genericTextStructure: {
                    show: false,
                    groupByLevel: false
                }
            },
            context: {},
            resultSet: {
                params: {},
                ids: []
            },
            defaultContext: {
                urls: {},
                index: 1,
                count: 1,
                filters: {}
            },
            imageIndex: null,
            annotationId: null,
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
            this.config.annotations.showLanguage && ret.push('language');
            this.config.annotations.showTypography && ret.push('typography');
            this.config.annotations.showOrthography && ret.push('orthography');
            this.config.annotations.showMorphology && ret.push('morphology');
            this.config.annotations.showLexis && ret.push('lexis');
            this.config.annotations.showMorphoSyntactical && ret.push('morpho_syntactical');

            this.bindEvents();
            return ret;
        },
        visibleAnnotationsByContext() {
            let annotations = this.text.annotations

            // filter by search context?
            if ( this.config.annotations.showOnlyInSearchContext && (this.context.filters ?? false) ) {
                annotations = this.annotationsFilterbyContext(annotations, this.context.params?.filters ?? {})
            }

            return annotations
        },
        visibleAnnotations() {
            if ( !this.config.annotations.show )
                return [];

            let annotations = this.visibleAnnotationsByContext

            // filter by config
            annotations = this.annotationsFilterByConfig(annotations)

            // sort & return
            return annotations.sort( function(annotation_1, annotation_2) {
                    return annotation_1.text_selection.selection_start - annotation_2.text_selection.selection_start
                });
        },
        visibleAnnotationsFormatted() {
            return this.visibleAnnotations.map( annotation => this.formatAnnotation(annotation) );
        },
        showAnnotationOptions() {
            return this.config.annotations.show || this.config.annotations.showList
        },
        hasSearchContext() {
           return Object.keys(this.context.params?.filters ?? {} ).length > 0
        },
        genericTextStructure() {
            let ret = {}

            ret = this.data.text.generic_text_structure
                .sort( (a,b) => a.text_selection.selection_start - b.text_selection.selection_start )

            return ret
        },
        genericTextStructureGroupedByLevel() {
            let ret = {}

            this.genericTextStructure.forEach( function(item) {
                    let level_number =  String(item?.text_level?.number || 0);
                    if (!(level_number in ret)) {
                        ret[level_number] = { ...item.text_level, ...{ children: [] } }
                    }
                    ret[level_number].children.push(item)
                });
            return ret;
        },
        textContainersOpen() {
            let conf = [
                this.config.text.show ? 1 : 0,
                this.config.text.showLemmas ? 1 : 0,
                this.config.text.showApparatus ? 1 : 0,
                this.config.translation.show ? 1 : 0,
                this.config.genericTextStructure.show ? 1 : 0
            ]
            return conf.reduce((partial_sum, a) => partial_sum + a, 0);
        },
        textContainerClass() {
            return this.textContainersOpen > 1 ? 'col-xs-12 col-md-6' : 'col-xs-12';
        }
    },
    methods: {
        annotationsFilterByConfig(annotations) {
            let that = this
            return annotations.filter( function(annotation) {
                return that.visibleAnnotationTypes.includes(annotation.type)
            } )
        },
        annotationsFilterbyContext(annotations, filters) {
            return annotations.filter( function(annotation) {
                // filter by type
                if ( (filters.annotation_type ?? false) && filters.annotation_type !== annotation.type ) {
                    return false
                }

                // filter by property
                let types = ['language', 'typography', 'orthography', 'lexis', 'morpho_syntactical','handshift']
                // console.log(filters)
                for ( const [key, value] of Object.entries(filters) ) {
                    let filterValues = Array.isArray(value) ? value : [ value ]
                    if ( types.includes(key.split('_')[0]) ) {
                        // check key
                        if ( !annotation.properties.hasOwnProperty(key) ) {
                            return false;
                        }

                        // check if values match
                        let propertieValues = Array.isArray(annotation.properties[key]) ? annotation.properties[key] : [ annotation.properties[key] ];
                        let valuesMatched = propertieValues.filter( function(item) {
                            return filterValues.includes(item.id)
                        })
                        return valuesMatched.length >= 1
                    }
                }

                return true
            } )
        },
        formatAnnotation(annotation) {
            return [
                annotation.text_selection.selection_start,
                annotation.text_selection.selection_end -1,
                { data: { id: annotation.type + ':' + annotation.id }, class: this.getAnnotationClass(annotation) }
            ]
        },
        getAnnotationClass(annotation) {
            return ['annotation', 'annotation-' + annotation.type, 'annotation-' + annotation.type + '-' + annotation.id].join(' ');
        },
        countAnnotationType(type) {
            return this.visibleAnnotationsByContext.filter( item => item.type === type ).length;
        },
        urlGeneratorIdName(url, filter) {
            return (value) => ( this.urls[url] + '?' + qs.stringify( { filters: {[filter]: value.id } } ) )
        },
        urlTmId(value) {
            return 'https://www.trismegistos.org/text/' + value
        },
        loadText(id) {
            axios.get(this.urls.text_get_single.replace('text_id',id)).then( (response) => {
                if (response.data) {
                    this.data.text = response.data;
                }
            })
        },
        clickAnnotation(e) {
            e.stopPropagation()

            let typeId = e.target?.dataset?.id;
            this.annotationId = typeId
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
        async updatePaginationIndex() {
            const { data } = await axios.get(this.context.urls.paginate + '?' + qs.stringify(this.resultSet.params) );
            this.resultSet.ids = data;
        },
        loadNextText() {
            let realNowIndex = (this.context.index - 1) - (Number.parseInt(this.resultSet.params.page) - 1)*Number.parseInt(this.resultSet.params.limit);
            let id = this.resultSet.ids[realNowIndex + 1]
            this.context.index += 1
            this.loadText(id)
        },
        loadPrevText() {
            let realNowIndex = (this.context.index - 1) - (Number.parseInt(this.resultSet.params.page) - 1)*Number.parseInt(this.resultSet.params.limit);
            let id = this.resultSet.ids[realNowIndex - 1]
            this.context.index -= 1
            this.loadText(id)
        }
    },
    mounted() {
        // make annotations clickable
        this.bindEvents();

        // update annotation events on config change
        this.$on('config-changed', function(config) {
            this.bindEvents();
        })

        // update context
        let context = {}
        try {
            let searchParams = new URLSearchParams(window.location.search);
            if ( searchParams.has('context') ) {
                context = JSON.parse(window.atob(searchParams.get('context')))
            }
        } catch (e) {
        }
        this.context = _merge(this.defaultContext, context)

        // update pagination
        this.resultSet.params = this.context.params

        // api calls better in created
        this.updatePaginationIndex()
    },
    created() {

    }
}
</script>

<style scoped lang="scss">

aside > div {
  background-color: #fafafa !important;
  border-left: 1px solid #e9ecef;

  .widget {
    border-bottom: 1px solid #e9ecef;
  }
}

</style>