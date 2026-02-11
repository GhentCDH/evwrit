export interface AnnotationOffsetConfig {
    start?: number;
    end?: number;
    startOverride?: number;
    endOverride?: number;
}

// Formats a Filemaker annotation for use in the AnnotatedText component
// Annotated Text: end offset is EXCLUSIVE
// Filemaker: end offset is EXCLUSIVE
export const formatAnnotatedTextAnnotation = (annotation: any, isActive: boolean = false, annotationOffsets: AnnotationOffsetConfig = null): any => {

    const weights = {
        "unit": 4,
        "subunit": 3,
        "element": 2,
        "modifier": 1,
    }

    let style = null;
    let weight = null;
    let render = isActive ? 'highlight-active' : 'highlight';
    style = annotation.type;
    switch (annotation.type) {
        case 'typography':
        case 'orthography':
        case 'language':
        case 'morpho_syntactical':
        case 'lexis':
        case 'morphology':
            style = annotation.type;
            break;
        case 'gtsa':
            render = 'underline';
            if ( annotation.properties?.gtsa_type?.name ) {
                style = annotation.properties.gtsa_type.name.toLowerCase();
                weight = weights[style] ?? 0;
            }
            break;
        case 'ltsa':
            render = 'underline';
            if ( annotation.properties?.ltsa_type?.name ) {
                style = annotation.properties.ltsa_type.name.toLowerCase();
                weight = weights[style] ?? 0;
            }
            break;
        case 'handshift':
            render = 'gutter';
            if ( annotation.internal_hand_num && annotation.internal_hand_num.match(/(\d+)/) ) {
                style = 'handshift-' + annotation.internal_hand_num.match(/(\d+)/)[0];
            }
            break;
    }

    const ret = {
        id: annotation.type + ':' + annotation.id,
        start: annotation.text_selection.selection_start,
        end: annotation.text_selection.selection_end,
        render,
        style,
    }
    if (annotationOffsets) {
        ret.start += (Number(annotation?.hasOverride ? annotationOffsets?.startOverride : annotationOffsets?.start) || 0);
        ret.end += (Number(annotation?.hasOverride ? annotationOffsets?.endOverride : annotationOffsets?.end) || 0);
    }

    if ( weight !== null ) {
        ret['weight'] = weight;
    }

    isActive && console.log(ret)

    return ret;
}

export const getAnnotationClass = (annotation: any, isActive: boolean = false, customClass: string = null): string => {
    let classes = [];
    switch(annotation.type) {
        case 'gtsa':
            if ( annotation.properties?.gtsa_type?.name ) {
                classes = classes.concat( ['annotation-' + annotation.type + '-' + annotation.properties.gtsa_type.name.toLowerCase()] );
            }
        case 'ltsa':
            if ( annotation.properties?.ltsa_type?.name ) {
                classes = classes.concat( ['annotation-' + annotation.type + '-' + annotation.properties.ltsa_type.name.toLowerCase()] );
            }
        case 'handshift':
            if ( annotation.internal_hand_num && annotation.internal_hand_num.match(/(\d+)/) ) {
                classes = classes.concat( ['annotation-handshift-' + annotation.internal_hand_num.match(/(\d+)/)[0]] );
            }
        default:
            classes = classes.concat(['annotation', 'annotation-' + annotation.type, 'annotation-' + annotation.type + '-' + annotation.id]);
    }
    // annotation active?
    if ( isActive ) {
        classes.push('annotation--active')
    }
    // custom class?
    if ( customClass ) {
        classes.push(customClass)
    }
    return classes.join(' ')
}