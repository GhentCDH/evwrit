<template>
    <div class="greek text" v-html="formatText(text)"></div>
</template>

<script>
export default {
    name: "GreekText",
    props: {
        text: {
            type: String,
        },
    },
    methods: {
        formatText(text) {
            const regexLineNumbers = /^([0-9]+)\./gm;
            const replaceLineNumbers = '<span class="line-number">$1 </span>'
            text = text.replace(regexLineNumbers, replaceLineNumbers);

            return text
        },
    }
}
</script>

<style scoped lang="scss">
</style>