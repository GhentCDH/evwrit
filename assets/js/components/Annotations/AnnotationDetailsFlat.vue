<template>
    <div class="annotation-details">
        <span v-for="(detail, index) in details">
            {{ index.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase() ) }}: {{ detail }}
        </span>
    </div>
</template>

<script>

import FormatValue from '../Sidebar/FormatValue'

export default {
    name: "AnnotationDetailsFlat",
    components: {
        FormatValue
    },
    props: {
        annotation: {
            type: Object,
            required: true
        },
        ignoreProperties: {
            type: Array,
            required: false,
            default: function() { return [
                'morpho_syntactical_cliticForm','morpho_syntactical_cliticContent','morpho_syntactical_cliticContext',
                'morpho_syntactical_caseForm','morpho_syntactical_caseContent','morpho_syntactical_caseContext',
                'morpho_syntactical_aspectForm','morpho_syntactical_aspectContent','morpho_syntactical_aspectContext',
                'morpho_syntactical_modalityForm','morpho_syntactical_modalityContent','morpho_syntactical_modalityContext',
            ] }
        },
        typeOnlyProperties: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    computed: {
        propertyKeys() {
            const keys = Object.keys(this.annotation.properties).sort()
                .filter(k => !this.ignoreProperties.includes(k))

            return this.typeOnlyProperties ? keys.filter(k => k.startsWith(this.annotation.type + '_')) : keys
        },
        details() {
            let ret = {};
            ret['original'] = this.annotation.text_selection.text
            //ret['selectionStart'] = this.annotation.text_selection.selection_start
            //ret['selectionEnd'] = this.annotation.text_selection.selection_end

            for (let prop of this.propertyKeys) {
                if (this.annotation.properties.hasOwnProperty(prop)) {
                    // remove boolean property
                    if ( ['has_handshift', 'textLevel'].includes(prop) ) {
                        continue;
                    }

                    let value = this.annotation.properties[prop]
                    prop = prop.split('_').slice(-1).join('') // strip type prefix
                    if ( value && Array.isArray(value) && value.length ) {
                        value.map( function(i) { if ( !i.name ) { /* console.log(i); console.log(value) */ } } )
                        ret[prop] = value.map( i => i.id_name.split('_').slice(1).join('_') ).join(', ')
                    } else if ( value && typeof value === 'object' && value.hasOwnProperty('id_name')) {
                        ret[prop] = value.id_name.split('_').slice(1).join('_')
                    }
                }
            }

            delete ret.textSelection
            // console.log(ret)
            return ret
        }
    }
}
</script>

<style scoped lang="scss">
.annotation-details span {
  display: inline-block;
  margin: 0 1rem 3px 0;
  color: #333;
  background-color: #efefef;
  padding: 2px 6px;
  font-size: 90%;
}
</style>