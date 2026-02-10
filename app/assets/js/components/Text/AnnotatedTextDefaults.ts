import {createAnnotationColor} from "@ghentcdh/annotated-text";

const createDefaultStyles = () => {
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
        "handshift-1": "#6200D1",
        "handshift-2": "#008D75",
        "handshift-3": "#FFBC00",
        "handshift-4": "#8AFF00",
        "handshift-5": "#FF7ECD",
        "handshift-6": "#005D1F",
        "handshift-7": "#F51772",
        "handshift-8": "#424600",
        "handshift-9": "#613990",
    }

    const styles = {}

    for (const [key, hexColor] of Object.entries(baseAnnotationColors)) {
        styles[key] = {
            color: createAnnotationColor(hexColor),
            borderRadius: "1px",
        }
    }

    for (const [key, hexColor] of Object.entries(structureAnnotationColors)) {
        styles[key] = {
            color: createAnnotationColor(hexColor)
        }
    }

    for (const [key, hexColor] of Object.entries(handShiftColors)) {
        styles[key] = {
            color: createAnnotationColor(hexColor)
        }
    }

    styles["default"] = createAnnotationColor("#808080")

    return styles
}

export default createDefaultStyles()
