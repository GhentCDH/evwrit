<script lang="ts">
import {type Annotation, AnnotationStyle} from '@ghentcdh/annotated-text';

export type RenderedAnnotation = Annotation & {
    render: "highlight" | "underline" | "gutter";
}
</script>

<script setup lang="ts">
import {ref, toRefs, watch, onMounted, onUnmounted, PropType} from 'vue'
import {createAnnotatedText, createHighlightStyle, type AnnotatedText, TextLineAdapter} from '@ghentcdh/annotated-text';
import defaultAnnotationStyles from './AnnotatedTextDefaults';

// Props
type Props = {
    text: string;
    annotations: RenderedAnnotation[];
    activeAnnotations: string[] | null;
    highlightedAnnotations: string[] | null;
    styles: Record<string, AnnotationStyle>;
    id: string | null;
    textOffset: number;
}

const props = defineProps({
    text: {
        type: String,
        required: true
    },
    annotations: {
        type: Array as PropType<RenderedAnnotation[]>,
        default: function() {
            return [];
        }
    },
    activeAnnotations: {
        type: Array as PropType<string[]>,
        default: function() {
            return [];
        }
    },
    highlightedAnnotations: {
        type: Array as PropType<string[]>,
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
    textOffset: {
        type: Number,
        default: 1
    }
})

// Emits
const emit = defineEmits(['annotation-click']);

const { text, annotations, textOffset, styles, highlightedAnnotations, activeAnnotations } = toRefs(props as Props);
const id: string = (props.id as string | null) || `annotated-text-${Math.random().toString(36).substring(2, 15)}`;

const annotatedText = ref<AnnotatedText<Annotation>>(null);

// Watchers
watch([text, annotations], ([newText, newAnnotations]) => {
    if (annotatedText.value) {
        annotatedText.value
            .setText(newText as string)
            .setAnnotations(newAnnotations as Annotation[]);
    }
});

watch(activeAnnotations, (newAnnotations, oldAnnotations) => {
    if (annotatedText.value) {
        annotatedText.value.selectAnnotations(newAnnotations);
    }
})

watch(highlightedAnnotations, (newAnnotations, oldAnnotations) => {
    if (annotatedText.value) {
        annotatedText.value.highlightAnnotations(newAnnotations);
    }
})

const onAnnotationClick = ({ mouseEvent: _mouseEvent, event: _event, data }) => {
    emit('annotation-click', data.annotation);
}

// Hooks
onMounted(() => {
    // todo: set lineOffset style property of text adapter
    annotatedText.value = createAnnotatedText(id as string)
    .setTextAdapter(TextLineAdapter())
    .setRenderParams({
        defaultRenderer: 'highlight',
        renderFn: (annotation: any): string | null => annotation?.render,
    })
    .registerStyle('default', ((styles.value as Record<string, AnnotationStyle>)?.default ?? {default: createHighlightStyle("#808080")}) as AnnotationStyle)
    .setStyleParams({
        styleFn: (annotation: any): string | null => {
            return annotation?.style;
        },
        defaultStyle: 'default'
    })
    .setAnnotationAdapter({ startOffset: textOffset.value as number })
    .registerStyles(styles.value as Record<string, AnnotationStyle>)
    .setText(text.value as string)
    .setAnnotations(annotations.value as Annotation[])
    .on('click', onAnnotationClick)
    .on('mouse-enter', () => {
        document.body.style.cursor = 'pointer';
    })
    .on('mouse-leave', () => {
        document.body.style.cursor = 'default';
    });

    console.log('text length', text.value.length)
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