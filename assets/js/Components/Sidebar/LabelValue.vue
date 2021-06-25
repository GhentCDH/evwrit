<template>
    <div class="labelvalue row">
        <div class="col-xs-6">
            {{ label }}
        </div>
        <div class="col-xs-6">
            <FormatValue v-if="value" :value="value" />
            <span v-else>{{ unknown }}</span>
        </div>
    </div>
</template>

<script>
import FormatValue from "./FormatValue";

export default {
    name: "LabelValue",
    components: {
        FormatValue
    },
    props: {
        label: {
            type: String,
        },
        value: {
            type: String|Number
        },
        unknown: {
            type: String,
            default: 'unknown'
        }
    },
}
</script>

<style scoped lang="scss">
</style>