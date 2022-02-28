<template>
    <div class="annotation__details">
        <LabelValue label="Original" :value="annotation.text_selection.text" :inline="false" value-class="greek" valueClass="greek" class="mbottom-small"></LabelValue>
        <LabelValue label="Annotation Type" :value="annotation.type" :inline="false" class="mbottom-small"></LabelValue>
        <LabelValue v-for="(value, label) in propertiesLabelValue" v-bind:key="label" :label="label" :value="value" :inline="true"></LabelValue>
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
        properties() {
            return Object.keys(this.annotation.properties).filter(k => k.startsWith(this.annotation.type + '_'))
        },
        propertiesLabelValue() {
            let ret = {};
            console.log(this.properties)
            for (const prop of this.properties) {
                let value = this.annotation.properties[prop]
                let label = prop.split('_').slice(-1).join('') // strip type prefix
                label = label.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase() )
                if ( value && Array.isArray(value) && value.length ) {
                    ret[label] = value.map( i => i.id_name.split('_').slice(1).join('_') ).join(', ')
                } else if ( value && typeof value === 'object' && value.hasOwnProperty('id_name')) {
                    ret[label] = value.id_name.split('_').slice(1).join('_')
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