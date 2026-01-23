<template>
    <div class="row">
        <CoolLightBox
                :items="imagesLightBox"
                :index="imageIndex"
                @close="imageIndex = null"
                overlay-color="rgba(30, 30, 30, .3)"
        >
        </CoolLightBox>
        <article class="col-sm-9">
            <div class="scrollable scrollable--vertical">
                <div class="mbottom-small">
                    <h1 class="inline_title">{{ text.title }}</h1>
                    <h5 class="padding-20 inline_title">{{text.id ? 'EVWRIT ID:' + text.id : ''}} {{text.id && text.tm_id ? '—' : ''}} {{text.tm_id ? 'TM ID:' + text.tm_id : ''}}</h5>
                </div>
               
                <div class="row mbottom-large">

                    <!-- Text -->
                    <div v-if="showText" :class="textContainerClass" class="text">
                        <h2>Text</h2>
                        <div class="row">
                            <div :class="textViewerClass" v-if="config.legacyMode">
                                <h3>Legacy viewer</h3>
                                <GreekText :text="text.text" :annotations="visibleAnnotationsFormatted" :annotation-offset="1"/>
                            </div>
                            <div :class="textViewerClass">
                                <h3 v-if="config.legacyMode">SVG viewer</h3>
                                <AnnotatedText :text="text.text"
                                               :annotations="visibleAnnotationsFormattedNew"
                                               @annotation-click="onClickAnnotationNew"
                                               :textOffset="1"
                                ></AnnotatedText>
                            </div>
                        </div>
                    </div>

                    <!-- Generic Text Structure -->
                    <div v-if="config.genericTextStructure.show && arrayIsNotEmpty(genericTextStructure)" :class="textContainerClass" class="text-structure">
                        <h2>Generic structure</h2>
                        <template v-if="config.genericTextStructure.groupByLevel">
                            <div :class="getLevelClass(getTextLevel(level.id))" v-for="level in genericTextStructureGroupedByLevel">
                                <label class="level__number" @click.stop="onClickLevel(getTextLevel(level.id))"><span>Level {{ level.number }} {{ level.type }}</span></label>
                                <label class="level__category" @click.stop="onClickLevel(getTextLevel(level.id))" v-if="level.id" v-for="category in formatLevelCategory(getTextLevel(level.id))"><span>{{ category }}</span></label>
                                <div :class="getStructureClass(textStructure)" v-for="textStructure in level.genericTextStructure">
                                    <label @click="onClickAnnotation(textStructure)"><span>{{ textStructure.properties.gts_part.name }} {{ textStructure.properties.gts_part.part_number}}</span></label>
                                    <div class="row">
                                        <div :class="textViewerClass" v-if="config.legacyMode">
                                            <h3>Legacy viewer</h3>
                                            <GreekText :text="textStructure.text_selection.text" :annotations="visibleAnnotationsFormattedNoLts" :annotation-offset="textStructure.text_selection.selection_start"></GreekText>
                                        </div>
                                        <div :class="textViewerClass">
                                            <h3 v-if="config.legacyMode">SVG viewer</h3>
                                            <AnnotatedText :text="textStructure.text_selection.text"
                                                           :annotations="visibleAnnotationsFormattedNoLtsNew"
                                                           @annotation-click="onClickAnnotationNew"
                                                           :textOffset="textStructure.text_selection.selection_start"
                                            ></AnnotatedText>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </template>
                        <template v-if="!config.genericTextStructure.groupByLevel">
                            <div :class="getStructureClass(textStructure)" v-for="textStructure in genericTextStructure">
                                <label @click="onClickAnnotation(textStructure)">
                                    <span v-if="textStructure.gts_textLevel">Level {{ textStructure.gts_textLevel.number }}</span>
                                    <span>{{ textStructure.properties.gts_part.name }} {{ textStructure.properties.gts_part.part_number}}</span>
                                </label>
                                <div class="row">
                                    <div :class="textViewerClass" v-if="config.legacyMode">
                                        <h3>Legacy viewer</h3>
                                        <GreekText :text="textStructure.text_selection.text" :annotations="visibleAnnotationsFormattedNoLts" :annotation-offset="textStructure.text_selection.selection_start"></GreekText>
                                    </div>
                                    <div :class="textViewerClass">
                                        <h3 v-if="config.legacyMode">SVG viewer</h3>
                                        <AnnotatedText :text="textStructure.text_selection.text"
                                                       :annotations="visibleAnnotationsFormattedNoLtsNew"
                                                       @annotation-click="onClickAnnotationNew"
                                                       :textOffset="textStructure.text_selection.selection_start"
                                        ></AnnotatedText>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>

                    <!-- Layout Text Structure -->
                    <div v-if="config.layoutTextStructure.show && arrayIsNotEmpty(layoutTextStructure)" :class="textContainerClass" class="text-structure">
                        <h2>Layout structure</h2>
                        <div :class="getStructureClass(textStructure)" v-for="textStructure in layoutTextStructure">
                            <label @click="onClickAnnotation(textStructure)">
                                <span>{{ textStructure.properties.lts_part.name }}</span>
                            </label>
                            <div :class="textViewerClass" v-if="config.legacyMode">
                                <h3>Legacy viewer</h3>
                                <GreekText :text="textStructure.text_selection.text" :annotations="visibleAnnotationsFormattedNoGts" :annotation-offset="textStructure.text_selection.selection_start"></GreekText>
                            </div>
                            <div :class="textViewerClass">
                                <h3 v-if="config.legacyMode">SVG viewer</h3>
                                <AnnotatedText :text="textStructure.text_selection.text"
                                               :annotations="visibleAnnotationsFormattedNoGts"
                                               @annotation-click="onClickAnnotationNew"
                                               :textOffset="textStructure.text_selection.selection_start"
                                ></AnnotatedText>
                            </div>
                        </div>
                    </div>

                    <!-- Lemmas -->
                    <div v-if="config.text.showLemmas && text.text_lemmas" :class="textContainerClass" class="text-lemmas">
                        <h2>Lemmas</h2>
                        <div class="row">
                            <div :class="textViewerClass" v-if="config.legacyMode">
                                <h3>Legacy viewer</h3>
                        <GreekText :text="text.text_lemmas" />
                            </div>
                            <div :class="textViewerClass">
                                <h3 v-if="config.legacyMode">SVG viewer</h3>
                                <AnnotatedText :text="text.text_lemmas" />
                            </div>
                        </div>
                    </div>

                    <!-- Apparatus -->
                    <div v-if="config.text.showApparatus && text.apparatus" :class="textContainerClass" class="text-lemmas">
                        <h2>Apparatus</h2>
                        <div class="row">
                            <div :class="textViewerClass" v-if="config.legacyMode">
                                <h3>Legacy viewer</h3>
                        <GreekText :text="text.apparatus" />
                            </div>
                            <div :class="textViewerClass">
                                <h3 v-if="config.legacyMode">SVG viewer</h3>
                                <AnnotatedText :text="text.apparatus" />
                            </div>
                        </div>
                    </div>

                    <!-- Translations -->
                    <div v-if="config.translation.show && arrayIsNotEmpty(text?.translation)" :class="textContainerClass" class="text-translations">
                        <div v-for="translation in text.translation" class="greek">
                            <h2>{{ translation.language.name}} Translation</h2>
                            <GreekText :text="translation.text"></GreekText>
                        </div>
                    </div>

                </div>

                <div class="row mbottom-large">
                    <!-- Annotations -->
                    <div v-if="config.annotations.showList" class="col-xs-12">
                        <h2 v-if="showText || config.text.showLemmas || config.genericTextStructure.show">Annotations</h2>
                        <div class="annotation-result" v-for="annotation in visibleAnnotations">
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
        <aside class="col-sm-3">
            <div class="widget-container scrollable scrollable--vertical" ref="sidebar">

                <Widget  title="Context" :collapsible="false" class="widget--sticky widget--metadata">
                    <div v-if="isValidResultSet()" class="row mbottom-default">
                      <div class="form-group">
                        <span class="btn btn-sm btn-primary" @click="navigateToSearchResult">&lt; Return to list</span>
                      </div>
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
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.expertMode" class="switch-primary" label="Advanced mode"></CheckboxSwitch>
                    </div>
                </Widget>

                <Widget title="Debug" class="widget--debug" v-if="isDebugMode && config.expertMode" :collapsed.sync="config.widgets.debug.isCollapsed">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.legacyMode" class="switch-primary" label="Show legacy viewer"></CheckboxSwitch>
                        <CheckboxSwitch v-model="config.annotations.showOverridesOnly" class="switch-primary" label="Only show annotations with overrides"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <label>Add Start offset</label> <input type="number" size="2" v-model="config.annotationOffsets.start"/> <br/>
                        <label>Add End offset</label> <input type="number" size="2" v-model="config.annotationOffsets.end"/> <br/>
                    </div>
                    <div class="form-group">
                        <label>Add Start offset (overrides)</label> <input type="number" size="2" v-model="config.annotationOffsets.startOverride"/><br/>
                        <label>Add End offset (overrides)</label> <input type="number" size="2" v-model="config.annotationOffsets.endOverride"/>
                    </div>
                    <AnnotationDetailsDebug v-if="selection.annotationId" :annotation="annotationsByTypeId[selection.annotationId]">
                    </AnnotationDetailsDebug>
                </Widget>

                <Widget title="Selection details" class="widget--selection-details" v-if="hasSelection" :collapsed.sync="config.widgets.selectionDetails.isCollapsed">
                    <AnnotationDetails v-if="selection.annotationId" :annotation="annotationsByTypeId[selection.annotationId]"
                                       :class="getAnnotationClass(annotationsByTypeId[selection.annotationId])"
                                       :url-generator="urlGeneratorIdName"
                                       :expertMode="config.expertMode"></AnnotationDetails>
                    <LevelDetails v-if="selection.levelId" :level="getTextLevel(selection.levelId)" :expertMode="config.expertMode"
                                  class="level-metadata"
                                  :expert-mode="config.expertMode"
                                  :url-generator="urlGeneratorIdName">
                    </LevelDetails>
                </Widget>

                <Widget title="Metadata" :collapsed.sync="config.widgets.metadata.isCollapsed">
                    <text-metadata :text="text" :url-generator="urlGeneratorIdName"></text-metadata>
                </Widget>

                <Widget title="Images" :count="arrayGetLength(images)" :collapsed.sync="config.widgets.images.isCollapsed" v-if="userIsAuthenticated">
                    <Gallery :images="imagesLightBox" :onClick="(index,url) => (imageIndex = index)" />
                </Widget>

                <Widget title="Translations" :count="arrayGetLength(text.translation)" :collapsed.sync="config.widgets.translations.isCollapsed">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.translation.show" class="switch-primary" label="Show translation(s)" :disabled="arrayGetLength(text.translation) === 0"></CheckboxSwitch>
                    </div>
                </Widget>

                <Widget title="Text options" :collapsed.sync="config.widgets.textOptions.isCollapsed">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.text.showLemmas" class="switch-primary" label="Show Lemmas" :disabled="text.text_lemmas === ''"></CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.text.showApparatus" class="switch-primary" label="Show Apparatus" :disabled="text.apparatus === ''"></CheckboxSwitch>
                    </div>
                </Widget>

                <Widget title="Materiality" :collapsed.sync="config.widgets.materiality.isCollapsed">
                    <PropertyGroup>
                        <LabelValue type="id_name" label="Production stage" :value="text?.production_stage" :url="urlGeneratorIdName('materiality_search','production_stage')"></LabelValue>
                        <LabelValue type="id_name" label="Material" :value="text?.material" :url="urlGeneratorIdName('materiality_search','material')"></LabelValue>
                        <LabelValue type="id_name" label="Writing direction" :value="text?.writing_direction" :url="urlGeneratorIdName('materiality_search','writing_direction')"></LabelValue>
                        <LabelValue type="id_name" label="Format" :value="text?.text_format" :url="urlGeneratorIdName('materiality_search','text_format')"></LabelValue>
                    </PropertyGroup>
                    <PropertyGroup>
                        <PageMetrics v-bind="text" unit="cm"></PageMetrics>
                    </PropertyGroup>
                    <PropertyGroup>
                        <LabelValue label="Lines" :value="arrayToRange(text?.lines)"  type="range"></LabelValue>
                        <LabelValue label="Lines (calculated)" :value="text?.count_lines_auto"></LabelValue>
                        <LabelValue label="Columns" :value="minMaxToRange(text?.columns)"  type="range"></LabelValue>
                        <LabelValue label="Letters per line" :value="minMaxToRange(text?.letters_per_line)" type="range"></LabelValue>
                        <LabelValue label="Letters per line (calculated)" :value="text?.letters_per_line_auto"></LabelValue>
                        <LabelValue label="Interlinear space" :value="text?.interlinear_space" ></LabelValue>
                        <LabelValue label="Line Height" :value="text?.image?.[0]?.line_height" ></LabelValue>
                    </PropertyGroup>
                </Widget>

                <Widget title="People" :collapsed.sync="config.widgets.attestation.isCollapsed" :count="arrayGetLength(people)">
                    <ancient-person-details v-for="person in people" :person="person" :key="'key_person:' + person.id"
                                            :export-mode="config.expertMode"
                                            :url-generator="urlGeneratorIdName"
                                            class="mbottom-small"
                    ></ancient-person-details>
                </Widget>

                <Widget title="Annotations" :collapsed.sync="config.widgets.annotations.isCollapsed" :count="countBaseAnnotations">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.show" class="switch-primary" label="Show annotations in text" :disabled="!countBaseAnnotations">
                            <span class="count pull-right">{{ countBaseAnnotations }}</span>
                        </CheckboxSwitch>
                    </div>

                    <div class="form-group mtop-small">
                        <CheckboxSwitch v-model="config.annotations.showList" class="switch-primary" label="Show annotation list below text" :disabled="!countBaseAnnotations"></CheckboxSwitch>
                    </div>
                    <div v-if="config.annotations.showList" class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showContext" class="switch-primary" label="Show annotation in text context"></CheckboxSwitch>
                    </div>
                    <div v-if="config.annotations.showList" class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showDetails" class="switch-primary" label="Show annotation metadata"></CheckboxSwitch>
                    </div>

                    <div v-if="showBaseAnnotations && countBaseAnnotations" class="mtop-small">
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.annotations.showTypography" class="switch-primary annotation-color-wrapper" label="Typography annotations">
                                <span class="count pull-right annotation-typography">{{
                                        countAnnotations('typography')
                                    }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.annotations.showOrthography" class="switch-primary annotation-color-wrapper" label="Orthography annotations">
                                <span class="count pull-right annotation-orthography">{{
                                        countAnnotations('orthography')
                                    }}</span>
                            </CheckboxSwitch>
                        </div>
                    </div>

                    <div v-if="showBaseAnnotations && countBaseAnnotations" class="mtop-small">
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.annotations.showLanguage" class="switch-primary annotation-color-wrapper" label="Language annotations">
                                <span class="count pull-right annotation-language">{{ countAnnotations('language') }}</span>
                            </CheckboxSwitch>
                        </div>
                    </div>

                    <div v-if="showBaseAnnotations && countBaseAnnotations" class="mtop-small">
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.annotations.showMorphoSyntactical" class="switch-primary annotation-color-wrapper" label="Syntax annotations">
                                <span class="count pull-right annotation-morpho_syntactical">{{
                                        countAnnotations('morpho_syntactical')
                                    }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.annotations.showLexis" class="switch-primary annotation-color-wrapper" label="Lexis annotations">
                                <span class="count pull-right annotation-lexis">{{ countAnnotations('lexis') }}</span>
                            </CheckboxSwitch>
                        </div>
                        <div class="form-group">
                            <CheckboxSwitch v-model="config.annotations.showMorphology" class="switch-primary annotation-color-wrapper" label="Morphology annotations">
                                <span class="count pull-right annotation-morphology">{{
                                        countAnnotations('morphology')
                                    }}</span>
                            </CheckboxSwitch>
                        </div>
                    </div>


                </Widget>

                <Widget title="Generic Structure" :collapsed.sync="config.widgets.genericTextStructure.isCollapsed" :count="arrayGetLength(genericTextStructure)">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.genericTextStructure.show" class="switch-primary" label="Show generic structure" :disabled="arrayGetLength(genericTextStructure) === 0">
                            <span class="count pull-right">{{ arrayGetLength(genericTextStructure) }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.genericTextStructure.groupByLevel" class="switch-primary" label="Reconstruct levels" :disabled="!countLevels">
                            <span class="count pull-right">{{ countLevels }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.genericTextStructure.showAnnotations" class="switch-primary" label="Show generic structure annotations" :disabled="!countAnnotations('gtsa')">
                            <span class="count pull-right">{{ countAnnotations('gtsa') }}</span>
                        </CheckboxSwitch>
                    </div>

                    <div v-if="showGTSA" class="mtop-small">
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

                <Widget title="Layout Structure" :collapsed.sync="config.widgets.layoutTextStructure.isCollapsed" :count="arrayGetLength(layoutTextStructure)">
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.layoutTextStructure.show" class="switch-primary" label="Show layout structure" :disabled="!arrayGetLength(layoutTextStructure)">
                            <span class="count pull-right">{{ countAnnotations('lts') }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.annotations.showHandshift" class="switch-primary" label="Show handwriting annotations" :disabled="!countAnnotations('handshift')">
                            <span class="count pull-right">{{ countAnnotations('handshift') }}</span>
                        </CheckboxSwitch>
                    </div>
                    <div class="form-group">
                        <CheckboxSwitch v-model="config.layoutTextStructure.showAnnotations" class="switch-primary" label="Show layout structure annotations" :disabled="!countAnnotations('ltsa')">
                            <span class="count pull-right">{{ countAnnotations('ltsa') }}</span>
                        </CheckboxSwitch>
                    </div>

                    <div v-if="showLTSA && countAnnotations('ltsa')" class="mtop-small">
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

                <Widget title="Links" :count="arrayGetLength(links)" :collapsed.sync="config.widgets.links.isCollapsed">
                    <div v-for="link in links">
                        <a :href="link.url" target="_blank">{{ link.title }}</a>
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
import Widget from '../Sidebar/Widget'
import LabelValue from '../Sidebar/LabelValue'
import PageMetrics from '../Sidebar/PageMetrics'
import GreekText from '../Text/GreekText'
import PropertyGroup from '../Sidebar/PropertyGroup'
import Gallery from '../Sidebar/Gallery'
import CheckboxSwitch from '../FormFields/CheckboxSwitch'
import AnnotationDetailsFlat from '../Annotations/AnnotationDetailsFlat'
import AnnotationDetailsDebug from '../Annotations/AnnotationDetailsDebug'
import AnnotationDetails from '../Annotations/AnnotationDetails'
import AncientPersonMetadata from "../Sidebar/AncientPersonMetadata.vue";
import LevelMetadata from "../Sidebar/LevelMetadata.vue";
import TextMetadata from "../Sidebar/TextMetadata.vue";

import PersistentConfig from "../Shared/PersistentConfig";
import ResultSet from "../Search/ResultSet";
import SearchSession from "../Search/SearchSession";
import SearchContext from "../Search/SearchContext";

import CoolLightBox from 'vue-cool-lightbox'
import 'vue-cool-lightbox/dist/vue-cool-lightbox.min.css'

import axios from 'axios'
import qs from 'qs'
import SharedSearch from "../Search/SharedSearch";

import AnnotatedText from "./AnnotatedText";

export default {
    name: "TextViewApp",
    components: {
        AnnotationDetailsDebug,
        Widget, LabelValue, PageMetrics, GreekText, CoolLightBox, PropertyGroup, Gallery, CheckboxSwitch, AnnotationDetailsFlat, AnnotationDetails,
        AncientPersonDetails: AncientPersonMetadata, LevelDetails: LevelMetadata, TextMetadata,
        AnnotatedText,
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
        },
        initUser: {
            type: String,
            required: true
        },
        debug: {
            type: Boolean|String,
            required: false,
            default: false
        },
    },
    data() {
        let data = {
            urls: JSON.parse(this.initUrls),
            data: JSON.parse(this.initData),
            user: JSON.parse(this.initUser),
            defaultConfig: {
                expertMode: false,
                legacyMode: false,
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
                    show: false,
                    showList: false,
                    showOnlyInSearchContext: true,
                    showDetails: true,
                    showContext: true,
                    showTypography: false,
                    showLanguage: false,
                    showOrthography: false,
                    showMorphology: false,
                    showLexis: false,
                    showMorphoSyntactical: false,
                    showHandshift: false,
                    showOverridesOnly: false,
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
                    selectionDetails: { isCollapsed: true },
                    metadata: { isCollapsed: true },
                    translations: { isCollapsed: true },
                    textOptions: { isCollapsed: true },
                    materiality: { isCollapsed: true },
                    attestation: { isCollapsed: true },
                    annotations: { isCollapsed: true },
                    genericTextStructure: { isCollapsed: true },
                    layoutTextStructure: { isCollapsed: true },
                    images: { isCollapsed: true },
                    links: { isCollapsed: true },
                    debug: { isCollapsed: true },
                },
                annotationOffsets: {
                    startOverride: 0,
                    endOverride: 0,
                    start: 0,
                    end: 0,
                }
            },
            selection: {},
            imageIndex: null,
            openRequests: false,
            indexNumberInputValue: null,
        }
        return data
    },
    computed: {
        isDebugMode() {
            return this.debug === true || this.debug === 'true'
        },
        userIsAuthenticated() {
            console.log('user', this.user)
            return this.user ? (this.user?.isAuthenticated ?? false) : false
        },
        userRoles() {
            return this.user ? (this.user?.roles ?? []) : [];
        },
        text: function() {
            return this.data.text
        },
        images: function() {
            return this.data.text?.image ?? []
        },
        imagesLightBox: function() {
            let result = []
            const images = this.data.text?.image ?? []
            if ( this.arrayGetLength(images) ) {
                images.forEach( function(image,index) {
                    result.push({
                        description: [
                            image.source ? 'Source: ' + image.source : null,
                            image.copyright ? 'Copyright: ' + image.copyright : null,
                        ].filter( (i) => i ).join(' - '),
                        src: 'https://media.evwrit.ugent.be/image.php?secret=RHRVvbV4ScZITUVjfbab85DCteR9dsPgw2s5G2aD&filename=' +image.filename
                    })
                })
            }
            return result
        },
        links: function() {
            const links = this.text?.link ?? []
            if ( this.text?.tm_id ) {
                links.push({
                    title: "Trismegistos",
                    url: `https://www.trismegistos.org/text/${this.text.tm_id}`
                })
            }
            return links
        },
        people: function() {
            if (this.text.ancient_person) {
                return this.text.ancient_person.filter(
                    person => this.arrayGetLength(person?.role) // && !['Unknown','unknown'].includes(person.role)
                )
            }
            return [];

        },
        annotationsByTypeId() {
            let result = []
            this.text.annotations.forEach( anno => result[anno.type+':'+anno.id] = anno )

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
        annotationsInContext() {
            let annotations = this.text?.annotations ?? []

            // filter by search context?
            if ( this.config.annotations.showOnlyInSearchContext && (this.context.params ?? false) ) {
                annotations = this.filterAnnotationsByContext(annotations, this.context.params ?? {})
            }

            return annotations
        },
        visibleAnnotations() {
            // filter by context
            let annotations = this.annotationsInContext

            // filter by config
            annotations = this.filterAnnotationsByConfig(annotations)

            // sort & return
            return annotations.sort( function(annotation_1, annotation_2) {
                    return annotation_1.text_selection.selection_start - annotation_2.text_selection.selection_start
                });
        },
        annotationsInContextByType() {
            const ret = {}
            this.annotationsInContext.forEach( annotation => ret[annotation.type] ? ret[annotation.type].push(annotation) : ret[annotation.type] = [ annotation ] )
            return ret
        },
        visibleAnnotationsFormatted() {
            return this.visibleAnnotations.reduce( (result, annotation) => result.concat(this.formatAnnotation(annotation)), [] );
        },
        visibleAnnotationsFormattedNew() {
            return this.visibleAnnotations.reduce( (result, annotation) => result.concat(this.formatAnnotationNew(annotation)), [] );
        },
        visibleAnnotationsFormattedNoGts() {
            return this.visibleAnnotations.reduce( (result, annotation) => result.concat(annotation.type != 'gtsa' ? this.formatAnnotation(annotation) : []), [] );
        },
        visibleAnnotationsFormattedNoGtsNew() {
            return this.visibleAnnotations.reduce( (result, annotation) => result.concat(annotation.type != 'gtsa' ? this.formatAnnotationNew(annotation) : []), [] );
        },
        visibleAnnotationsFormattedNoLts() {
            return this.visibleAnnotations.reduce( (result, annotation) => result.concat(annotation.type != 'ltsa' ? this.formatAnnotation(annotation) : []), [] );
        },
        visibleAnnotationsFormattedNoLtsNew() {
            return this.visibleAnnotations.reduce( (result, annotation) => result.concat(annotation.type != 'ltsa' ? this.formatAnnotationNew(annotation) : []), [] );
        },
        showText() {
            return  (!this.config.genericTextStructure.show || !this.genericTextStructure.length) && (!this.config.layoutTextStructure.show || !this.layoutTextStructure.length)
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
            const annotations = this.data.text?.annotations ?? []

            return annotations
                .filter( function(annotation) {
                    return annotation.type === 'gts'
                })
                .sort( (a,b) => a.text_selection.selection_start - b.text_selection.selection_start )
        },
        // get layout text structure annotations
        layoutTextStructure() {
            const annotations = this.data.text?.annotations ?? []

            return annotations
                .filter( function(annotation) {
                    return annotation.type === 'lts'
                })
                .sort( (a,b) => a.text_selection.selection_start - b.text_selection.selection_start )
        },
        // group text structure annotations by level number
        // catch: not all annotations have a level assigned
        genericTextStructureGroupedByLevel() {
            let ret = {}

            this.genericTextStructure.forEach( function(annotation) {
                if ( !annotation?.properties?.gts_textLevel?.id ) {
                    return
                }
                let level_number =  String(annotation.properties.gts_textLevel.number)
                let level_properties = annotation.properties.gts_textLevel
                if (!(level_number in ret)) {
                    ret[level_number] = { ...level_properties, ...{ genericTextStructure: [] } }
                }
                ret[level_number].genericTextStructure.push(annotation)
            });

            return Object.values(ret).sort( (a,b) => a.number - b.number );
        },
        baseAnnotations() {
            const types = ['typography', 'orthography','language','morpho_syntactical', 'lexis', 'morphology']
            return this.annotationsInContext.filter( annotation => types.includes(annotation.type) );
        },
        countBaseAnnotations() {
            return this.baseAnnotations.length
        },
        countLevels() {
            return Object.keys(this.genericTextStructureGroupedByLevel).length
        },
        textContainersOpen() {
            let conf = [
                this.showText ? 1 : 0,
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
        },
        textViewerClass() {
            return this.config.legacyMode ? 'col-xs-6' : 'col-xs-12'
        },
        hasSelection() {
            return Object.values(this.selection).filter(item => item).length !== 0
        },

    },
    methods: {
        openSelectionWidget() {
            this.config.widgets.selectionDetails.isCollapsed = false
            this.$refs.sidebar.scrollTop = 0;
        },
        resetSelection() {
            Object.keys(this.selection).map( key =>
                this.$set(this.selection, key, null)
            )
        },
        onClickAnnotationNew(annotation) {
            if (this.selection?.annotationId === annotation.id )
                this.$set(this.selection, 'annotationId', null)
            else {
                this.resetSelection()
                this.$set(this.selection, 'annotationId', annotation.id)
                this.openSelectionWidget()
            }
        },
        onClickAnnotation(annotation) {
            if (this.selection?.annotationId === this.getAnnotationTypeId(annotation) )
                this.$set(this.selection, 'annotationId', null)
            else {
                this.resetSelection()
                this.$set(this.selection, 'annotationId', this.getAnnotationTypeId(annotation))
                this.openSelectionWidget()
            }
        },
        onClickLevel(level) {
            if (this.selection.levelId === level.id )
                this.$set(this.selection, 'levelId', null)
            else {
                this.resetSelection()
                this.$set(this.selection, 'levelId', level.id)
                this.openSelectionWidget()
            }
        },
        clickAnnotation(e) {
            e.stopPropagation()

            // get annotation id
            let typeId = e.target?.dataset?.id;
            if ( typeId ) {
                if (this.selection?.annotationId === typeId )
                    this.$set(this.selection, 'annotationId', null)
                else {
                    this.resetSelection()
                    this.$set(this.selection, 'annotationId', typeId)
                    this.openSelectionWidget()
                }
            }
        },
        getAnnotationTypeId(annotation) {
            return annotation.type+':'+annotation.id
        },
        annotationHasContext(annotation) {
           return !!annotation?.context
        },
        filterAnnotationsByConfig(annotations) {
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
                // filter by overrides only (if set)
                .filter( function(annotation) {
                    return !that.config.annotations.showOverridesOnly || ( that.config.annotations.showOverridesOnly && (annotation?.hasOverride ?? false) )
                })
        },
        filterAnnotationsByContext(annotations, context_params) {
            // todo: add support for operators
            let annotationTypeFilter = context_params?.annotation_type?.value ?? []

            let annotationPropertyPrefixes = ['language', 'typography', 'orthography', 'lexis', 'morpho_syntactical','handshift','ltsa','gtsa', 'gts', 'lts']
            let annotationPropertyFilters = {}
            for ( const [key, param] of Object.entries(context_params) ) {
                for ( const prefix of annotationPropertyPrefixes ) {
                    if (key.startsWith(prefix + '_') ) {
                        annotationPropertyFilters[key] = Array.isArray(param.value) ? param.value : [ param.value ]
                    }
                }
            }

            let that = this
            return annotations.filter( function(annotation) {
                // filter only annotations in scope of annotationTypeFilter
                if ( !annotationTypeFilter.includes(annotation.type) && annotationTypeFilter.length !== 0) {
                    return false
                }

                // filter annotations in scope
                for (const [contextParam, values] of Object.entries(annotationPropertyFilters)) {

                    // check if intersection exists between property values of annotation and parameter values of context
                    let valuesMatched = values.filter( function(value) {
                        // console.log([contextParam, values, that.contextAnnotationMapper(annotation, contextParam)])
                        return that.contextAnnotationMapper(annotation, contextParam).includes(value)
                    })
                    if ( valuesMatched.length === 0 ) {
                        return false
                    }
                }

                return true
            } )
        },
        // maps a context parameter to the corresponding values of the annotation property
        contextAnnotationMapper(annotation, contextParam) {
            let propertyValues = [];
            let props = annotation.properties
            switch(contextParam) {
                case 'annotation_type':
                    return [annotation.type]
                case 'gts_textLevel':
                case 'textLevel':
                    if (!Array.isArray(props?.gts_textLevel))
                        return [props.gts_textLevel?.number]
                    return props.gts_textLevel.map( i => i.number )
                default:
                    if(!Array.isArray(props[contextParam]))
                        return [props[contextParam]?.id]
                    return props[contextParam].map( i => i.id )
            }
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
        formatAnnotationNew(annotation) {

            const weights = {
                "unit": 4,
                "subunit": 3,
                "element": 2,
                "modifier": 1,
            }

            let style = null;
            let weight = null;
            let render = 'highlight';
            style = annotation.type;
            switch (annotation.type) {
                case 'typography':
                case 'orthography':
                case 'language':
                case 'morpho_syntactical':
                case 'lexis':
                case 'morphology':
                    style = annotation.type;
                    break;
                case 'gtsa':
                    render = 'underline';
                    if ( annotation.properties?.gtsa_type?.name ) {
                        style = annotation.properties.gtsa_type.name.toLowerCase();
                        weight = weights[style] ?? 0;
                    }
                    break;
                case 'ltsa':
                    render = 'underline';
                    if ( annotation.properties?.ltsa_type?.name ) {
                        style = annotation.properties.ltsa_type.name.toLowerCase();
                        weight = weights[style] ?? 0;
                    }
                    break;
                case 'handshift':
                    render = 'gutter';
                    if ( annotation.internal_hand_num && annotation.internal_hand_num.match(/(\d+)/) ) {
                        style = 'handshift-' + annotation.internal_hand_num.match(/(\d+)/)[0];
                    }
                    break;
            }

            const ret = {
                id: annotation.type + ':' + annotation.id,
                start: annotation.text_selection.selection_start + parseInt(annotation?.hasOverride ? this.config.annotationOffsets.startOverride : this.config.annotationOffsets.start),
                end: annotation.text_selection.selection_end + parseInt(annotation?.hasOverride ? this.config.annotationOffsets.endOverride : this.config.annotationOffsets.end),
                render,
                style,
            }

            if ( weight !== null ) {
                ret.weight = weight;
            }

            return ret;
        },
        getAnnotationClass(annotation, extra = null) {
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
            // annotation active?
            if ( this.selection?.annotationId ) {
                if ( this.getAnnotationTypeId(annotation) === this.selection?.annotatonId ) {
                    classes.push('annotation--active')
                }
            }
            // extra's?
            if ( extra ) {
                classes.push(extra)
            }
            return classes.join(' ')
        },
        getLevelClass(level) {
            let classes = ['level']
            if (this.selection?.levelId && this.selection.levelId === level.id ) {
                classes.push('level--active')
            }
            return classes.join(' ')
        },
        getStructureClass(annotation) {
            let classes = ['structure']
            if (this.selection?.annotationId && this.selection.annotationId === this.getAnnotationTypeId(annotation) ) {
                classes.push('structure--active')
            }
            return classes.join(' ')
        },
        getTextLevel(level_id) {
            return this.text?.text_level?.find(level => level.id === level_id )
        },
        formatLevelCategory(level) {
            if (!level) {
                return []
            }
            return (this.getTextLevel(level.id)?.level_category ?? []).map( category => [ category?.level_category_category?.name ].filter(name => name).join(', ') ).filter( label => label )
        },
        countAnnotations(type = null) {
            if ( type ) {
                return this.annotationsInContextByType[type]?.length ?? 0
            }
            return this.annotationsInContext.length
        },
        countGtsType(type) {
            return this.annotationsInContext.filter(item => item.properties?.gtsa_type?.name === type ).length;
        },
        countLtsType(type) {
            return this.annotationsInContext.filter (item => item.properties?.ltsa_type?.name === type ).length;
        },
        urlGeneratorIdName(url, filter) {
            return (value) => ( this.getUrl(url) + '?' + qs.stringify( { filters: {[filter]: value.id } } ) )
        },
        getUrl(route) {
            return this.urls[route] ?? ''
        },
        getTextUrl(id) {
            let url = this.urls['text_get_single'].replace('text_id', id);
            if (this.isValidContext()) {
                let hash = this.getContextHash();
                url += '#' + hash;
                this.saveContextHash(hash, this.context)
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
        minMaxToRange(value) {
            if ( value ) {
                return {start: value.min, end: value.max}
            }
            return null;
        },

        loadTextByIndex(index) {
            let that = this;
            if ( !this.resultSet.count ) return;

            // reset selection
            that.selection = {};

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
        },
        arrayGetLength(data) {
            return Array.isArray(data) ? data.length : 0;
        },
        arrayIsNotEmpty(data) {
            return Array.isArray(data) && data.length > 0;
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
}

</style>