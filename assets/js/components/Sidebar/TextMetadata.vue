<template>
    <div class="text__metadata">
        <LabelValue label="EVWRIT ID" :value="text.id"></LabelValue>
        <LabelValue label="Trismegistos ID" :value="text.tm_id" :url="getTmTextUrl"></LabelValue>

        <PropertyGroup v-if="text.text_type">
            <LabelValue label="Type" :value="text.text_type" type="id_name"></LabelValue>
            <LabelValue label="Subtype" :value="text.text_subtype" type="id_name"></LabelValue>
        </PropertyGroup>

        <PropertyGroup v-if="text.archive">
            <LabelValue label="Archive" :value="text.archive" type="id_name"
                        :url="urlGenerator('text_search', 'archive')"
            ></LabelValue>
        </PropertyGroup>

        <PropertyGroup>
            <LabelValue label="Date" :value="{start: text.year_begin, end: text.year_end}" type="range"></LabelValue>
            <LabelValue label="Era" :value="text.era" type="id_name" :url="urlGenerator('text_search', 'era')"></LabelValue>
        </PropertyGroup>

        <PropertyGroup>
            <LabelValue label="Location written" :value="text.location_written" type="id_name"
                        :url="urlGenerator('text_search', 'location_written')"></LabelValue>
            <LabelValue label="Location found" :value="text.location_found" type="id_name"
                        :url="urlGenerator('text_search', 'location_found')"></LabelValue>
        </PropertyGroup>

        <LabelValue v-if="text.keyword" label="Keywords" :value="text.keyword" :url="urlGenerator('text_search', 'keyword')" type="id_name"></LabelValue>
    </div>
</template>

<script>
import LabelValue from './LabelValue'
import PropertyGroup from "./PropertyGroup.vue";

export default {
    name: "TextMetadata",
    components: {
        LabelValue, PropertyGroup
    },
    props: {
        text: {
            type: Object,
            required: true
        },
        expertMode: {
            type: Boolean,
            default: false
        },
        urlGenerator: {
            type: Function,
            default: null,
            required: true
        }
    },
    computed: {},
    methods: {
        getTmTextUrl(id) {
            return 'https://www.trismegistos.org/text/' + id
        },
    }
}
</script>

<style scoped lang="scss">
</style>