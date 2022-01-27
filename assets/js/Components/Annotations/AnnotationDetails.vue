<template>
    <div class="annotation__details">
        <LabelValue label="Original" :value="annotation.text_selection.text" :inline="false" value-class="greek" class="greek mbottom-small"></LabelValue>
        <LabelValue label="Annotation Type" :value="annotation.type" :inline="false"></LabelValue>
        <LabelValue v-for="(value, label) in details" v-bind:key="label" :label="label" :value="value" :inline="false"></LabelValue>

<!--            <span v-for="(detail, index) in details">-->
<!--                {{ index.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase() ) }}: {{ detail }}-->
<!--            </span>-->
    </div>
</template>

<script>

import LabelValue from '../Sidebar/LabelValue'

export default {
    name: "AnnotationDetails",
    components: {
        LabelValue
    },
    props: {
        annotation: {
            type: Object,
            required: true
        },
    },
    computed: {
        details() {
            let ret = {};

            for (let prop in this.annotation.properties) {
                if (this.annotation.properties.hasOwnProperty(prop)) {
                    // remove boolean property
                    if (prop === 'has_handshift') {
                        continue;
                    }

                    let value = this.annotation.properties[prop]
                    let label = prop.split('_').slice(1).join('')
                    label = label.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase() )
                    if ( value && Array.isArray(value) && value.length ) {
                        ret[label] = value.map( i => i.id_name.split('_').slice(1).join('_') ).join(', ')
                    } else if ( value && typeof value === 'object' && value.hasOwnProperty('id_name')) {
                        ret[label] = value.id_name.split('_').slice(1).join('_')
                    }
                }
            }

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