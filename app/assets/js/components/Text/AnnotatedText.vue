<script lang="ts">
import {type Annotation, AnnotationStyle} from '@ghentcdh/annotated-text';

export type RenderedAnnotation = Annotation & {
    render: "highlight" | "underline" | "gutter";
}

</script>


<script setup lang="ts">
import {computed, ref, toRefs, watch, onMounted, onUnmounted, PropType} from 'vue'
import {createAnnotatedText, TextLineAdapter, UnderLineAnnotationRender, GutterAnnotationRender, TextAnnotationRender} from '@ghentcdh/annotated-text';
import {defaultAnnotationStyles} from './AnnotatedTextDefaults';

// Props
const props = defineProps({
    text: {
        type: String,
        required: true
    },
    annotations: {
        type: Array<RenderedAnnotation>,
        default: function() {
            return [];
        }
    },
    styles: {
        type: Object as PropType<Record<string, AnnotationStyle>>,
        default: function() {
            return defaultAnnotationStyles;
        }
    },
    id: {
        type: String,
        default: null
    },
    annotationOffset: {
        type: Number,
        default: 0
    }
})

// Emits
const emit = defineEmits<{
   'annotation-click': (annotation: Annotation) => void
}>();

const { text, annotations, annotationOffset, styles } = toRefs(props);
const id = props.id || `annotated-text-${Math.random().toString(36).substring(2, 15)}`;

const annotatedText = ref(null);

// Computed
const adjustedAnnotations = computed(() => {
    return annotations.value.map((i: Annotation) => {
        return {
            ...i,
            start: i.start - annotationOffset.value,
            end: i.end - annotationOffset.value,
        }
    })
});

// Watchers
watch([text, adjustedAnnotations], ([newText, newAnnotations]) => {
    if (annotatedText.value) {
        annotatedText.value
            .setText(newText)
            .setAnnotations(newAnnotations);
    }
});

const onAnnotationClick = ({ mouseEvent, event, data }) => {
    emit('annotation-click', data.annotation);
}

const customRenderFn = (annotation: RenderedAnnotation) => {
    console.log(annotation)
    if (annotation?.target === "gutter") {
        return GutterAnnotationRender;
    }
    if (annotation.id.toString().startsWith('gtsa')) {
        return UnderLineAnnotationRender;
    }
    if (annotation.id.toString().startsWith('ltsa')) {
        return UnderLineAnnotationRender;
    }

    return TextAnnotationRender;
};


// Hooks
onMounted(() => {
    annotatedText.value = createAnnotatedText(id, {
        text: TextLineAdapter(),
        annotation: {
            style: {
                styleFn: (annotation) => {
                    return annotation.style ?? "default";
                }
            },
            render: {
                renderFn: (annotation) => annotation.render,
            },
        }
    })
    .registerStyles(styles.value)
    .updateRenderStyle('highlight', {
        borderWidth: 1,
        borderRadius: 2,
        padding: 4,
    })
    .setText(text.value)
    .setAnnotations(adjustedAnnotations.value as Annotation[])
    .on('click', onAnnotationClick)
    .on('mouse-enter', () => {
        document.body.style.cursor = 'pointer';
    })
    .on('mouse-leave', () => {
        document.body.style.cursor = 'default';
    });
})

onUnmounted(() => {
    if (annotatedText.value) {
        annotatedText.value.destroy();
    }
})
</script>

<template>
    <div :id="id" class="annotated-text greek-text-lines">
    </div>
</template>

<style lang="scss">
</style>