<template>
    <div class="text__metadata">
        <PropertyGroup>
            <LabelValue label="EVWRIT ID" :value="text.id"></LabelValue>
            <LabelValue label="Trismegistos ID" :value="text.tm_id" :url="getTmTextUrl"></LabelValue>
        </PropertyGroup>

        <PropertyGroup v-if="levelCategories.length">
            <LabelValue label="Text type">
                <div v-for="category in levelCategories" class="span-list span-list--comma-separated">
                    <FormatValue type="id_name" :value="category.level_category_category"></FormatValue>
                    <template v-if="category.level_category_subcategory">
                        (<FormatValue type="id_name" :value="category.level_category_subcategory"></FormatValue>)
                    </template>
                </div>
            </LabelValue>
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
import FormatValue from "./FormatValue.vue";

export default {
    name: "TextMetadata",
    components: {
        FormatValue,
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
    computed: {
        levelCategories() {
            let level_categories = []
            this.text?.text_level?.forEach( text_level => level_categories = level_categories.concat(text_level?.level_category ?? []) )
            level_categories = level_categories.filter(category => category)
            return level_categories
                .filter((category, index) => {
                    return index === level_categories.findIndex( o => o.id === category.id )
            })
        }
    },
    methods: {
        getTmTextUrl(id) {
            return 'https://www.trismegistos.org/text/' + id
        },
    }
}
</script>

<style scoped lang="scss">
</style>