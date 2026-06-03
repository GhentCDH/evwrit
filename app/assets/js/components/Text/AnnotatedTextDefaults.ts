import {
    createHighlightStyle,
    createGutterStyle,
    createUnderlineStyle,
    CustomAnnotationStyle,
    AnnotationStyle
} from "@ghentcdh/annotated-text";

const createDefaultStyles = (): Record<string, AnnotationStyle> => {
    const defaultHighlightStyle = {
        borderWidth: 0.5,
        borderRadius: 2,
        borderOpacity: 0.5,
        backgroundOpacity: 0.1,
    }

    const defaultActiveHighlightStyle = {
        ...defaultHighlightStyle,
        borderWidth: 1,
        borderOpacity: 0.9,
        // backgroundColor: '#ffffff',
        backgroundOpacity: 0.4,
    }

    const defaultUnderlineStyle = {
        backgroundOpacity: 0,
        borderWidth: 1.5,
    }

    const defaultActiveUnderlineStyle = {
        ...defaultUnderlineStyle,
        backgroundOpacity: 0,
        borderWidth: 3,
        borderOpacity: 0.9,
    }

    const defaultGutterStyle = {
        backgroundOpacity: 0.6,
        borderWidth: 0,
    }

    const defaultActiveGutterStyle = {
        ...defaultGutterStyle,
        backgroundOpacity: 1,
        borderWidth: 2,
        borderOpacity: 1,
    }

    const baseAnnotationColors = {
        "orthography": "#f58231",
        "typography": "#e61919",
        "morpho_syntactical": "#18aa2a",
        "lexis": "#f032e6",
        "language": "#1E64C8",
        "morphology": "#5d2802",
    }

    const structureAnnotationColors = {
        "unit": "#ff0000",
        "subunit": "#ffA500",
        "element": "#008000",
        "modifier": "#add8e6",
    }

    const handShiftColors = {
        "handshift_1": "#6200D1",
        "handshift_2": "#008D75",
        "handshift_3": "#FFBC00",
        "handshift_4": "#8AFF00",
        "handshift_5": "#FF7ECD",
        "handshift_6": "#005D1F",
        "handshift_7": "#F51772",
        "handshift_8": "#424600",
        "handshift_9": "#613990",
    }

    const styles = {}

    for (const [key, hexColor] of Object.entries(baseAnnotationColors)) {
        styles[key] = {
            default: createHighlightStyle(hexColor, defaultHighlightStyle),
            hover: createHighlightStyle(hexColor, defaultActiveHighlightStyle),
            active: createHighlightStyle(hexColor, defaultActiveHighlightStyle),
        }
    }

    for (const [key, hexColor] of Object.entries(structureAnnotationColors)) {
        styles[key] = {
            default: createUnderlineStyle(hexColor, defaultUnderlineStyle),
            hover: createHighlightStyle(hexColor, defaultActiveUnderlineStyle),
            active: createHighlightStyle(hexColor, defaultActiveUnderlineStyle),
        }
    }

    for (const [key, hexColor] of Object.entries(handShiftColors)) {
        styles[key] = {
            default: createGutterStyle(hexColor, defaultGutterStyle),
            hover: createGutterStyle(hexColor, defaultActiveGutterStyle),
            active: createGutterStyle(hexColor, defaultActiveGutterStyle),
        }
    }

    styles["default"] = createHighlightStyle("#808080", defaultHighlightStyle)

    return styles
}

export default createDefaultStyles()
