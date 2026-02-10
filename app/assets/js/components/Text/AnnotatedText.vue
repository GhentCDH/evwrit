<script lang="ts">
import {type Annotation, AnnotationStyle} from '@ghentcdh/annotated-text';

export type RenderedAnnotation = Annotation & {
    render: "highlight" | "underline" | "gutter";
}
</script>


<script setup lang="ts">
import {ref, toRefs, watch, onMounted, onUnmounted, PropType} from 'vue'
import {createAnnotatedText, TextLineAdapter} from '@ghentcdh/annotated-text';
import defaultAnnotationStyles from './AnnotatedTextDefaults';

// Props
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

const { text, annotations, textOffset, styles } = toRefs(props);
const id = props.id || `annotated-text-${Math.random().toString(36).substring(2, 15)}`;

const annotatedText = ref(null);

// Watchers
watch([text, annotations], ([newText, newAnnotations]) => {
    if (annotatedText.value) {
        annotatedText.value
            .setText(newText as string)
            .setAnnotations(newAnnotations as Annotation[]);
    }
});

const onAnnotationClick = ({ mouseEvent: _mouseEvent, event: _event, data }) => {
    emit('annotation-click', data.annotation);
}

// Hooks
onMounted(() => {
    // todo: set lineOffset style property of text adapter
    annotatedText.value = createAnnotatedText(id as string, {
        text: TextLineAdapter({
            textOffset: textOffset.value as number
        }),
        annotation: {
            style: {
                // @ts-nocheck
                styleFn: (annotation: any): string => {
                    return ((annotation.style as string) ?? "default");
                }
            },
            render: {
                renderFn: (annotation: any): string => annotation.render as string,
            },
        },
    } as any)
    .registerStyles(styles.value as Record<string, AnnotationStyle>)
    .updateRenderStyle('highlight', {
        borderWidth: 1,
        borderRadius: 2,
        padding: 4,
    } as any)
    .setText(text.value as string)
    .setAnnotations(annotations.value as Annotation[])
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