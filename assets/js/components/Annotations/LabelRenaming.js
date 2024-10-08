import Vue from 'vue'

export default {
    data() {
        return {
            labelMapper: {
                'Bigraphism Comments': 'Transliteration Comments',
                'Bigraphism Domain': 'Transliteration Domain',
                'Bigraphism Formulaicity': 'Transliteration Formulaicity',
                'Bigraphism Type': 'Transliteration Type',
                'Bigraphism Rank': 'Transliteration Rank',
                'Accronym': 'Acronym'
            }
        }
    },
    methods: {
        renameLabel(oldLabel) {
            return this.labelMapper[oldLabel] ?? oldLabel;
        }
    },
    mounted() {
        
    },
}
