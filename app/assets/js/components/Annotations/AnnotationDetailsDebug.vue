<template>
    <div class="annotation-metadata">
        <LabelValue label="Annotation start" :value="annotation.text_selection.selection_start" :inline="true"
                    class="mtop-small"></LabelValue>
        <LabelValue label="Annotation end" :value="annotation.text_selection.selection_end" :inline="true"></LabelValue>

        <LabelValue label="Annotation start (original)" :value="annotation?.text_selection_original?.selection_start"
                    :inline="true" class="mtop-small"></LabelValue>
        <LabelValue label="Annotation end (original)" :value="annotation?.text_selection_original?.selection_end"
                    :inline="true"></LabelValue>

        <pre>
            {{ JSON.stringify(annotation, null, 2) }}
        </pre>
    </div>
</template>

<script>

import LabelValue from '../Sidebar/LabelValue'

export default {
    name: "AnnotationDetailsDebug",
    components: {
        LabelValue
    },
    props: {
        annotation: {
            type: Object,
            required: true
        },
    },
}
</script>

<style scoped lang="scss">
</style>