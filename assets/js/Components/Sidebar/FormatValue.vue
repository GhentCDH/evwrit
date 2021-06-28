<template>
    <span v-if="url">
        <a :href="url">{{ value }}</a>
    </span>
    <span v-else-if="value != null">
        {{ value }}
    </span>
    <span v-else>
        {{ unknown }}
    </span>
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
        }
    },
}
</script>

<style scoped lang="scss">
</style>