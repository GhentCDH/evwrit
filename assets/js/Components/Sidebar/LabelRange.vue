<template>
    <div class="labelvalue row">
        <div class="col-xs-6">
            {{ label }}
        </div>
        <div class="col-xs-6">
            <FormatValue :value="formatValue(value)" />
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
            type: Array
        },
        unknown: {
            type: String,
            default: 'unknown'
        },
        url_generator: {
            type: Function,
            default: null
        },
        separator: {
            type: String,
            default: ' - '
        }
    },
    methods: {
        formatValue(value) {
            if ( Array.isArray(value) ) {
                return (value[0] === value[1] ? value[0] : value[0] + this.separator + value[1])
            } else {
                return this.unknown
            }
        }
    }
}
</script>

<style scoped lang="scss">
</style>