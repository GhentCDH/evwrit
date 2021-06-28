<template>
    <div>
        <CoolLightBox
                :items="images"
                :index="imageIndex"
                @close="imageIndex = null">
        </CoolLightBox>
        <article class="col-sm-9">
            <h1>{{ text.title }}</h1>
            <GreekText :text="text.text" />
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

import CoolLightBox from 'vue-cool-lightbox'
import 'vue-cool-lightbox/dist/vue-cool-lightbox.min.css'

import axios from 'axios'
import qs from 'qs'



export default {
    name: "TextViewApp",
    components: {
        Widget, LabelValue, PageMetrics, LabelObject, GreekText, CoolLightBox, LabelRange, PropertyGroup, Gallery
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
        }
    },
    methods: {
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

<style scoped>
.text {
    white-space: pre-line;
}
</style>