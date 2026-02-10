<template>
    <div class="greek-text" v-html="formatText(text,filteredAnnotations)" :class="{ 'greek-text--compact': compact}"></div>
</template>

<script>
import { FlattenRanges } from 'etali'

export default {
    name: "GreekText",
    props: {
        text: {
            type: String,
            required: true
        },
        annotations: {
            type: Array,
            default: function() {
                return [];
            }
        },
        annotationOffset: {
            type: Number,
            default: 1
        },
        compact: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        filteredAnnotations: function() {
            // increase end position by one (needed for FlattenRanges) and correct by offset
            // todo: check why max is needed
            let annos = this.annotations.map((i) => [
                Math.max(0, i[0] - this.annotationOffset),
                i[1] + 1 - this.annotationOffset, i[2]
            ])

            annos = annos.concat(this.getLineNumberAnnotations(this.text))

            // flatten and reverse ranges
            annos = FlattenRanges(annos).reverse();

            // remove line-number annotations
            annos = annos.filter(
                (i) => i[2].filter((details) => details.type === "lineNumber").length === 0
            )

            // console.log(annos);

            return annos;
        },
    },
    methods: {
        formatText(text, annotations = []) {

            // split text into lines
            let lines = text.split("\n");

            // walk lines
            let line_start = 0;
            let line_end = 0;
            let line;
            let i = 0;
            for (i = 0; i < lines.length; i++) {
                line = lines[i];

                // line end = line start + length - 1 + 1 newline char
                line_end = line_start + (line.length - 1) + 1;

                // annotate line
                line = this.annotateLine(line, line_start, annotations);

                // add line structure
                line = this.structureLine(line);

                // console.log([line, line_start, line_end, annotations]);

                lines[i] = line;
                line_start = line_end + 1;
            }

            // return text
            return lines.join("\n");
        },
        structureLine(line) {
            const regLineNumber = /^([0-9\/]+[a-z]?)\./g;
            const replaceLineNumber =
                '<span class="greek-text__line-number">$1</span>[GUTTER]<span class="greek-text__text">';

            let output = ''

            let text = line.text;
            if (text.match(regLineNumber)) {
                output = text.replace(regLineNumber, replaceLineNumber) + "</span>";
            } else {
                output = '<span class="greek-text__line-number"></span>[GUTTER]<span class="greek-text__text">' + text + "</span>";
            }

            output = output.replace('[GUTTER]', '<span class="greek-text__gutter">' + line.gutter + '</span>')

            return '<div class="greek-text__line">' + output + '</div>';
        },
        insertBefore(text, index, replacement) {
            return text.substring(0, index) + replacement + text.substring(index);
        },
        intersectInterval(a, b) {
            const min = a[0] < b[0] ? a : b;
            const max = min == a ? b : a;

            //min ends before max starts -> no intersection
            if (min[1] < max[0]) return null; //the ranges don't intersect

            return [max[0], min[1] < max[1] ? min[1] : max[1]];
        },
        annotateLine(line, line_start, annotations) {
            // caculate line end position
            // line end = line start + length - 1 + 1 newline char
            const line_end = line_start + (line.length - 1) + 1;

            let gutterAnnotations = []
            let htmlGutter = ''

            // walk annotations and check line intersection
            let i;
            for (const annotation of annotations) {
                // adjust anno end and line end
                if (
                    (i = this.intersectInterval(
                        [annotation[0], annotation[1] - 1],
                        [line_start, line_end - 1]
                    ))
                ) {

                    gutterAnnotations = gutterAnnotations.concat(annotation[2].filter( anno => anno.class.includes('annotation-handshift') ))
                    let tokenAnnotations = annotation[2].filter( anno => !anno.class.includes('annotation-handshift') )

                    if ( tokenAnnotations.length ) {
                        let globalClassses = tokenAnnotations.map( (i) => (i.class) ).join(" ");
                        let htmlSuffix = '</span>'.repeat(tokenAnnotations.length + 1)
                        let htmlPrefix = '<span class="annotation-wrapper ' + globalClassses + '">'

                        htmlPrefix = tokenAnnotations.reduce( function(html, i) {
                            let props = Object.entries(i.data ?? {}).map( i => `data-${i[0]}="${i[1]}"` ).join(' ');
                            html += '<span class="' + i.class + '" ' + props + '>'
                            return html
                        }, htmlPrefix);

                        line = this.insertBefore(line, i[1] - line_start + 1, htmlSuffix);
                        line = this.insertBefore(
                            line,
                            i[0] - line_start,
                            htmlPrefix
                        );
                    }

                }
            }

            // gutter annotations?
            if (gutterAnnotations.length) {
                let ret = {}
                gutterAnnotations = Object.values(gutterAnnotations.reduce( function(ret, anno) {
                    ret[anno.data?.id] = anno
                    return ret
                }, {}))

                htmlGutter = gutterAnnotations.reduce( function(html, i) {
                    let props = Object.entries(i.data ?? {}).map( i => `data-${i[0]}="${i[1]}"` ).join(' ');
                    html += '<span class="' + i.class + '" ' + props + '></span>'
                    return html
                }, htmlGutter);
            }

            // remove (*)
            line = line.replace('(*)','')

            // remove (hand x)

            return {
                text: line,
                gutter: htmlGutter
            };
        },
        // create line number annotations
        // end position is next character, needed for FlattenRanges
        getLineNumberAnnotations(text) {
            const regLineNumbers = /^([0-9\/]+[a-z]?\.)/gm

            let annos = []
            let ln
            while ((ln = regLineNumbers.exec(text))) {
                annos.push([ln.index, ln.index + ln[0].length - 1 + 1, { type: "lineNumber" }]);
            }

            return annos
        }
    }
}
</script>

<style scoped lang="scss">
</style>