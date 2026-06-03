import qs from 'qs'

const BASE_URLS = {
    gtsa:        '/textstructure/search',
    ltsa:        '/textstructure/search',
    orthography: '/annotation/orthotypo/search',
    typography:  '/annotation/orthotypo/search',
    syntax:      '/annotation/linguistic/search',
    morphology:  '/annotation/linguistic/search',
    lexis:       '/annotation/linguistic/search',
    language:    '/annotation/language/search',
}

export class AnnotationSearchUrlGenerator {
    constructor(annotation) {
        this.annotation = annotation
    }

    canGenerate(type) {
        return type in BASE_URLS
    }

    generate(type, filter) {
        if (!this.canGenerate(type)) return null

        return (value) => {
            const filters = []

            if (/^(typography|orthography|morphology|lexis|language)_/.test(filter)) {
                filters.push(qs.stringify({ filters: { annotation_type: this.annotation.type } }))
            }
            if (type === 'syntax') {
                filters.push(qs.stringify({ filters: { annotation_type: 'morpho_syntactical' } }))
            }
            if (/^gtsa_subtype$/.test(filter)) {
                filters.push(qs.stringify({ filters: { gtsa_type: this.annotation.properties.gtsa_type.id } }))
            }
            if (/^ltsa_subtype$/.test(filter)) {
                filters.push(qs.stringify({ filters: { gtsa_type: this.annotation.properties.ltsa_type.id } }))
            }
            if (/^ltsa_part$/.test(filter)) {
                filters.push(qs.stringify({ filters: { lts_part: value.id } }))
            }
            if (/^gtsa_part$/.test(filter)) {
                filters.push(qs.stringify({ filters: { gts_part: value.id } }))
            }
            filters.push(qs.stringify({ filters: { [filter]: value.id } }))

            return BASE_URLS[type] + '?' + filters.join('&')
        }
    }
}
