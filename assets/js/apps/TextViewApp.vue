<template>
    <div>
        <CoolLightBox
                :items="images"
                :index="imageIndex"
                @close="imageIndex = null">
        </CoolLightBox>
        <article class="col-sm-9">
            <h1>{{ text.title }}</h1>
            <div class="hidden">
                <b-button class="btn" @click="gotoNextText()">prev</b-button>
                <b-button class="btn" href="">back to search</b-button>
                <b-button class="btn" @click="gotoPrevText()">next</b-button>
            </div>
            <div class="form-group form-inline">
                <CheckboxSwitch v-model="config.text.show" class="switch-primary" label="Show text"></CheckboxSwitch>
                <CheckboxSwitch v-model="config.text.showLemmas" class="switch-primary" label="Show lemmas"></CheckboxSwitch>
                <CheckboxSwitch v-model="config.text.showLemmasAside" v-if="config.text.showLemmas" class="switch-primary" label="Show lemmas aside"></CheckboxSwitch>
                <CheckboxSwitch v-model="config.annotations.show" class="switch-primary" label="Show annotations"></CheckboxSwitch>
                <CheckboxSwitch v-model="config.annotations.showContext" v-if="config.annotations.show" class="switch-primary" label="Show annotation context"></CheckboxSwitch>
                <CheckboxSwitch v-model="config.annotations.showDetails" v-if="config.annotations.show" class="switch-primary" label="Show annotation details"></CheckboxSwitch>
            </div>
            <div class="row">
                <div v-if="config.text.show" :class="{ 'col-md-6': config.text.showLemmas && config.text.showLemmasAside }" class="col-xs-12">
                    <GreekText :text="text.text" :annotations="visibleAnnotationsFormatted" :annotation-offset="1"/>
                </div>
                <div :class="{ 'col-md-6': config.text.showLemmas && config.text.showLemmasAside }" class="col-xs-12">
                    <h2 v-if="config.text.showLemmas && !config.text.showLemmasAside">Lemmas</h2>
                    <GreekText :text="text.text_lemmas" v-if="config.text.showLemmas"  />
                </div>
                <div class="col-xs-12">
                    <h2 v-if="config.text.show || config.text.showLemmas">Annotations</h2>
                    <div class="annotation-result" v-for="annotation in visibleAnnotations">
                        <GreekText
                                v-show="config.annotations.showContext"
                                :text="annotation.context.text"
                                :annotations="[ formatAnnotationContext(annotation) ]"
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
            <div class="bg-tertiary padding-default">

                <Widget title="Metadata">
                    <LabelValue label="ID" :value="text.id"></LabelValue>
                    <LabelValue label="Trismegistos ID" :value="text.tm_id"></LabelValue>
                    <LabelRange label="Date" :value="[text.year_begin, text.year_end]"></LabelRange>
                    <LabelObject label="Era" :value="text.era" :url_generator="urlSearch('text_search', 'era')"></LabelObject>
                    <LabelObject v-if="text.keyword" label="Keywords" :value="text.keyword" :url_generator="urlSearch('text_search', 'keyword')"></LabelObject>
                </Widget>

                <Widget title="Materiality" :init-open="false">
                    <PropertyGroup>
                        <LabelObject label="Production stage" :value="text.production_stage" :url_generator="urlSearch('materiality_search','production_stage')"></LabelObject>
                        <LabelObject label="Material" :value="text.material" :url_generator="urlSearch('materiality_search','material')"></LabelObject>
                        <LabelObject label="Writing direction" :value="text.writing_direction" :url_generator="urlSearch('materiality_search','writing_direction')"></LabelObject>
                        <LabelObject label="Format" :value="text.text_format" :url_generator="urlSearch('materiality_search','text_format')"></LabelObject>
                    </PropertyGroup>
                    <PropertyGroup>
                        <PageMetrics v-bind="text"></PageMetrics>
                    </PropertyGroup>
                    <PropertyGroup>
                        <LabelRange label="Lines" :value="text.lines"></LabelRange>
                        <LabelRange label="Columns" :value="text.columns"></LabelRange>
                        <LabelRange label="Letters per line" :value="text.letters_per_line"></LabelRange>
                        <LabelValue label="Interlinear space" :value="text.interlinear_space" ></LabelValue>
                    </PropertyGroup>
                </Widget>

                <Widget title="Attestation" :init-open="false">
                </Widget>

                <Widget title="Annotations" :init-open="false" :count="text.annotations.length">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.show" class="switch-primary" label="Show annotations"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showContext" class="switch-primary" label="Show annotation context"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showDetails" class="switch-primary" label="Show annotation details"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showLanguage" class="switch-primary" label="Show Language annotations">
                            <span class="count">{{ countAnnotationType('language') }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showTypography" class="switch-primary" label="Show Typography annotations">
                            <span class="count">{{ countAnnotationType('typography') }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showMorphoSyntactical" class="switch-primary" label="Show Morpho-Syntactical annotations">
                            <span class="count">{{ countAnnotationType('morpho_syntactical') }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showOrthography" class="switch-primary" label="Show Orthography annotations">
                            <span class="count">{{ countAnnotationType('orthography') }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showLexis" class="switch-primary" label="Show Lexis annotations">
                            <span class="count">{{ countAnnotationType('lexis') }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showMorphology" class="switch-primary" label="Show Morphology annotations">
                            <span class="count">{{ countAnnotationType('morphology') }}</span>
                        </CheckboxSwitch>
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

                <Widget title="Translations" :count="text.translation.length" :init-open="false">
                    <div v-for="translation in text.translation">
                        <em>{{ translation.language.name}}</em>
                        <span>{{translation.text}}</span>
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
import LabelObject from '../Components/Sidebar/LabelObject'
import LabelRange from '../Components/Sidebar/LabelRange'
import PageMetrics from '../Components/Sidebar/PageMetrics'
import GreekText from '../Components/Shared/GreekText'
import PropertyGroup from '../Components/Sidebar/PropertyGroup'
import Gallery from '../Components/Sidebar/Gallery'
import CheckboxSwitch from '../Components/Shared/CheckboxSwitch'
import AnnotationDetailsFlat from '../Components/Annotations/AnnotationDetailsFlat'

import CoolLightBox from 'vue-cool-lightbox'
import 'vue-cool-lightbox/dist/vue-cool-lightbox.min.css'

import axios from 'axios'
import qs from 'qs'

export default {
    name: "TextViewApp",
    components: {
        Widget, LabelValue, PageMetrics, LabelObject, GreekText, CoolLightBox, LabelRange, PropertyGroup, Gallery, CheckboxSwitch, AnnotationDetailsFlat
    },
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
            config: {
                text: {
                    show: true,
                    showLemmas: true,
                    showLemmasAside: true
                },
                annotations: {
                    show: true,
                    showDetails: true,
                    showContext: true,
                    showTypography: true,
                    showLanguage: true,
                    showOrthography: true,
                    showMorphology: true,
                    showLexis: true,
                    showMorphoSyntactical: true
                }
            },
            imageIndex: null
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
                    result.push('https://media.evwrit.ugent.be/image.php?secret=RHRVvbV4ScZITUVjfbab85DCteR9dsPgw2s5G2aD&filename=' + image.filename)
                })
            }
            return result
        },
        visibleAnnotationTypes() {
            let ret = [];
            this.config.annotations.showLanguage && ret.push('language');
            this.config.annotations.showTypography && ret.push('typography');
            this.config.annotations.showOrthography && ret.push('orthography');
            this.config.annotations.showMorphology && ret.push('morphology');
            this.config.annotations.showLexis && ret.push('lexis');
            this.config.annotations.showMorphoSyntactical && ret.push('morpho_syntactical');
            return ret;
        },
        visibleAnnotations() {
            let that = this;

            if ( !this.config.annotations.show )
                return [];

            let ret = this.text.annotations
                .filter( function(annotation) {
                    return that.visibleAnnotationTypes.includes(annotation.type)
                }).sort( function(annotation_1, annotation_2) {
                    return annotation_1.text_selection.selection_start - annotation_2.text_selection.selection_start
                });
            return ret;
        },
        visibleAnnotationsFormatted() {
            return this.visibleAnnotations.map( annotation => this.formatAnnotation(annotation) );
        }
    },
    methods: {
        formatAnnotation(annotation) {
            return [
                annotation.text_selection.selection_start,
                annotation.text_selection.selection_end -1,
                { data: { id: annotation.id, type: annotation.type }, class: 'annotation annotation-' + annotation.type }
            ]
        },
        formatAnnotationContext(annotation) {
            return [
                annotation.text_selection.selection_start,
                annotation.text_selection.selection_end - 1,
                { id: annotation.id, type: annotation.type, class: 'annotation annotation-' + annotation.type }
            ]
        },
        countAnnotationType(type) {
            return this.data.text.annotations.filter( item => item.type === type ).length;
        },
        urlSearch(url, filter) {
            return (value) => ( this.urls[url] + '?' + qs.stringify( { filters: {[filter]: value } } ) )
        },
        async loadText(id) {
            const result = await axios.get(this.urls.text_get_single.replace('text_id',id))
            if (result.data) {
                this.data.text = result.data;
            }
        },
    }
}
</script>

<style scoped lang="scss">
.text {
    white-space: pre-line;
}

.annotation-result {
    padding: 8px 0;
    border-bottom: 1px solid #ccc;
    marging: -1px 0 -1px 0;

    &:first-child {
         border-top: 1px solid #ccc;
    }

  .annotation-details {
    margin-top: 10px
  }
}

.checkbox-switch .count {
  background-color: white;
  color: black;
  display: inline-block;
  border: 1px solid #d1d1d1;
  padding: 3px 8px;
  margin-left: 0.5em;
  border-radius: 5px;
  font-size: 80%;
  line-height: 1;
}
</style>