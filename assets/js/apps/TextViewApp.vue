<template>
    <div>
        <article class="col-sm-9">
            <h1>{{ text.title }}</h1>
            <div class="greek text" v-html="formatText(text.text)"></div>
            <a @click="loadText(32110)">prev</a>
            <a @click="loadText(54692)">next</a>
            <div v-for="translation in text.translation" class="text">{{ translation.text }}</div>
        </article>
        <aside class="col-sm-3">
            <div class="bg-tertiary padding-default">

                <Widget title="Metadata">
                    <LabelValue label="ID" :value="text.id"></LabelValue>
                    <LabelValue label="Trismegistos ID" :value="text.tm_id"></LabelValue>
                    <LabelValue label="Date" :value="text.year_begin + ' - ' + text.year_end"></LabelValue>
                    <LabelObject label="Era" :value="text.era" :url_generator="urlSearch('text_search', 'era')"></LabelObject>
                    <LabelObject v-if="text.keyword" label="Keywords" :value="text.keyword" :url_generator="urlSearch('text_search', 'keyword')"></LabelObject>
                </Widget>

                <Widget title="Materiality" :init-open="false">
                    <LabelObject label="Production stage" :value="text.production_stage" :url_generator="urlSearch('materiality_search','production_stage')"></LabelObject>
                    <LabelObject label="Writing direction" :value="text.writing_direction" :url_generator="urlSearch('materiality_search','writing_direction')"></LabelObject>
                    <LabelObject label="Format" :value="text.text_format" :url_generator="urlSearch('materiality_search','text_format')"></LabelObject>
                    <LabelObject label="Material" :value="text.material" :url_generator="urlSearch('materiality_search','material')"></LabelObject>
                    <PageMetrics v-bind="text"></PageMetrics>
                </Widget>

                <Widget title="Images" :init-open="false">
                    <div v-for="image in text.image">
                        <img :src="'https://media.evwrit.ugent.be/image.php?secret=RHRVvbV4ScZITUVjfbab85DCteR9dsPgw2s5G2aD&filename=' + image.filename" style="width: 100%">
                    </div>
                </Widget>

                <Widget title="Links" :init-open="false">
                    <div v-for="link in text.link">
                        <a :href="link.url">{{ link.title }}</a>
                    </div>
                </Widget>

                <Widget title="Translations" :init-open="false">
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

import PageMetrics from '../Components/Sidebar/PageMetrics'

import axios from 'axios'



export default {
    name: "TextViewApp",
    components: {
        Widget, LabelValue, PageMetrics, LabelObject
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
            data: JSON.parse(this.initData)
        }
        return data
    },
    computed: {
        text: function() {
            return this.data.text
        }
    },
    methods: {
        outputValue: function(value, format = null) {
            if ( ['string','number'].includes(typeof(value)) ) {
                return value;
            } else if ( typeof(value) == 'object' && value.hasOwnProperty('name') ) {
                return value.name
            }

            return null
        },
        urlSearch(url, filter) {
            return (value) => ( this.urls[url] + '?' + filter + '=' + value )
        },
        formatText(text) {
            const regexLineNumbers = /^([0-9]+)\./gm;
            const replaceLineNumbers = '<span class="line-number">$1 </span>'
            text = text.replace(regexLineNumbers, replaceLineNumbers);

            return text
        },
        async loadText(id) {
            const result = await axios.get(this.urls.text_get_single.replace('text_id',id))
            if (result.data) {
                this.data.text = result.data;
            }
        }

    }
}
</script>

<style scoped>
.text {
    white-space: pre-line;
}
</style>