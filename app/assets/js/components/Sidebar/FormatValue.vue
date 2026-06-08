<template>
    <span class="format-value" v-if="value != null">
        <span class="prefix" v-if="prefix">{{ prefix }}</span>
        <span class="value" v-if="url">
            <a v-if="url" :href="url">{{formatValue(value)}}</a>
        </span>
        <span class="value" v-if="!url">{{formatValue(value)}}</span>
        <span class="suffix" v-if="suffix">{{ suffix }}</span>
    </span>
    <span  class="format-value format-value--unknown" v-else>{{unknown}}</span>
</template>

<script>
export default {
    name: "FormatValue",
    props: {
        value: {
            type: String|Number
        },
        unknown: {
            type: String,
            default: null
        },
        url: {
            type: String,
        },
        type: {
            type: String,
        },
        decimals: {
            type: Number,
            default: 2
        },
        prefix: {
            type: String,
            default: null
        },
        suffix: {
            type: String,
            default: null
        }
    },
    methods: {
        formatValue(value) {
            if ( !value ) {
                return this.unknown
            }

            switch (this.type) {
                case 'range':
                    if ( value?.start && value?.end ) {
                        return (value.start === value.end ? value.start : value.start + ' - ' + value.end)
                    }
                    break;
                case 'id_name':
                    if ( value?.name ) {
                        return String(value.name).trim()
                    }
                    break;
                case 'number':
                    let num = value
                    if ( typeof value !== 'number' ) {
                        num = Number(value)
                    }
                    if (Number.isNaN(num)) {
                        return this.unknown
                    }
                    if (!Number.isInteger(num)) {
                        return num.toFixed(this.decimals)
                    }
                    return value
                default:
                    // check if value is a float. If value is float, round (max 2 decimals)
                    if ( typeof value === 'number' && !Number.isInteger(value) ) {
                        return value.toFixed(this.decimals)
                    }
                    return this.locale ? String(value[this.locale]).trim() : String(value).trim()
            }

            return this.unknown;
        }
    }
}
</script>

<style scoped lang="scss">
</style>