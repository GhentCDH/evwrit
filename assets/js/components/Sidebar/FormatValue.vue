<template>
    <span v-if="value != null">
        <a v-if="url" :href="url">{{formatValue(value)}}</a>
        <template v-else>{{formatValue(value)}}</template>
    </span>
    <span v-else>{{unknown}}</span>
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
            default: 'unknown'
        },
        url: {
            type: String,
        },
        type: {
            type: String,
        },
        locale: {
            type: String,
            default: null
        },
        secondLocale: {
            type: String,
            default: null
        },
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
                        let ret = (this.locale && value?.name?.[this.locale]) ? String(value.name[this.locale]).trim() : String(value.name).trim()
                        ret = this.secondLocale && value?.name?.[this.secondLocale] ? ret + ' (' + value.name[this.secondLocale] + ')' : ret
                        return ret
                    }
                    break;
                default:
                    return this.locale ? String(value[this.locale]).trim() : String(value).trim()
            }

            return this.unknown;
        }
    }
}
</script>

<style scoped lang="scss">
</style>