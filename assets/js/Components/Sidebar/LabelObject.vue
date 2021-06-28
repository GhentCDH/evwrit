<template>
    <div class="labelvalue row property">
        <div class="col-xs-6 property-label">
            {{ label }}
        </div>
        <div class="col-xs-6 property-value">
            <FormatValue v-if="Array.isArray(value) && value.length" v-for="item in value" :key="item.id" :value="item.name" :url="url_generator ? url_generator(item.id) : null" />
            <FormatValue v-if="value" :value="value.name" :url="url_generator ? url_generator(value.id) : null" />
            <span v-else="unknown">{{ unknown }}</span>
        </div>
    </div>
</template>

<script>

import FormatValue from './FormatValue'

export default {
    name: "LabelObject",
    components: {
        FormatValue
    },
    props: {
        label: {
            type: String,
        },
        value: {
            type: Object|Array
        },
        unknown: {
            type: String,
            default: 'unknown'
        },
        url_generator: {
            type: Function,
            default: null
        }
    },
}
</script>

<style scoped lang="scss">
.property {
    margin-bottom: 2px;

    .property-value {
        span {
          display: block;
        }
    }

}
</style>