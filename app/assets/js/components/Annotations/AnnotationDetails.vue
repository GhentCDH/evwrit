<template>
    <div class="annotation-metadata">
        <LabelValue label="Annotation ID" :value="annotation.id" :inline="true" v-if="expertMode"></LabelValue>
        <LabelValue label="Annotation Type" :value="annotationType" :inline="true" class="mbottom-small"></LabelValue>
        <LabelValue label="Text" v-if="expertMode && annotation.text_selection.text" :value="annotation.text_selection.text_edited" :inline="false" value-class="greek" valueClass="greek" class="mbottom-small" ></LabelValue>
        <LabelValue label="Edited" v-if="expertMode && annotation.text_selection.text_edited" :value="annotation.text_selection.text_edited" :inline="false" value-class="greek" valueClass="greek" class="mbottom-small"></LabelValue>
        <LabelValue v-for="prop in propertyKeys" v-bind:key="prop"
                    :label="propertyLabel(prop)"
                    :value="propertyValue(prop)"
                    :inline="true"
                    :class="propertyClass(prop)"
                    :url="generateUrl(annotationType, prop)"
                    type="id_name"></LabelValue>

        <LabelValue label="Preservation status" v-if="annotation.lts_preservationStatus" :value="annotation.lts_preservationStatus" :inline="true" class="mtop-small"></LabelValue>
        <LabelValue label="Preservation status" v-if="annotation.gts_preservationStatus" :value="annotation.gts_preservationStatus" :inline="true" class="mtop-small"></LabelValue>

        <LabelValue label="Person" v-if="annotation.gts_preservationStatus" :value="annotation.gts_preservationStatus" :inline="true" class="mtop-small"></LabelValue>

        <template v-if="annotation.ancient_person">
            <h3>Person</h3>
            <AncientPersonDetails :person="annotation.ancient_person"
                                  :url-generator="urlGenerator">
            </AncientPersonDetails>
        </template>
    </div>
</template>

<script>

import LabelValue from '../Sidebar/LabelValue'
import LabelRenaming from './LabelRenaming'
import AncientPersonDetails from "../Sidebar/AncientPersonMetadata.vue";
import qs from "qs";

export default {
    name: "AnnotationDetails",
    components: {
        AncientPersonDetails,
        LabelValue
    },
    mixins: [
        LabelRenaming,
    ],
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
                'lts_preservationStatus', 'gts_preservationStatus'
            ] }
        },
        expertMode: {
            type: Boolean,
            default: false
        },
        urlGenerator: {
            type: Function,
            default: null,
            required: true
        },
        propertyWeights: {
            type: Object,
            required: false,
            default: function() {
                return {
                    handshift_scriptType: 100,
                    handshift_degreeOfFormality: 102,
                    handshift_expansion: 103,
                    handshift_slope: 104,
                    handshift_curvature: 105,
                    handshift_connectivity: 106,
                    handshift_orientation: 107,
                    handshift_regularity: 108,
                    handshift_lineation: 109,

                    handshift_punctuation: 110,
                    handshift_accentuation: 111,
                    handshift_wordSplitting: 112,
                    handshift_abbreviation: 113,
                    handshift_correction: 114,
                }
            }
        },
        propertyClasses: {
            type: Object,
            required: false,
            default: function() {
               return {
                   handshift_lineation: 'mbottom-small'
               }
            }
        }
    },
    data() {
        return {
            // TODO: is not very clean, but idk where to get the urls from otherwise
            urls: {
                'gtsa': "http://localhost:8080/textstructure/search",
                'ltsa': "http://localhost:8080/textstructure/search",
                'orthography': "http://localhost:8080/annotation/orthotypo/search",
                'typography': "http://localhost:8080/annotation/orthotypo/search",
            }
        }
    },
    computed: {
        propertyKeys() {
            return Object.keys(this.annotation.properties)
                .filter(k => k.startsWith(this.annotation.type + '_'))
                .filter(k => !this.ignoreProperties.includes(k))
                .sort( (a,b) => (this.propertyWeights[a] ?? 0) - (this.propertyWeights[b] ?? 0) )
        },
        annotationType() {
            return this.annotation.type.replace('morpho_syntactical','syntax')
        },
        propertiesLabelValue() {
            let ret = {};
            for (const prop of this.propertyKeys) {
                let value = this.annotation.properties[prop]
                let label = prop.split('_').slice(-1).join('') // strip type prefix
                label = label.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase() )
                // Check if label uses old expression that has since been renamed
                label = this.renameLabel(label);
                if ( value && Array.isArray(value) && value.length ) {
                    ret[label] = value.map( i => i.id_name.split('_').slice(1).join('_') ).join(', ')
                } else if ( value && typeof value === 'object' && value.hasOwnProperty('id_name')) {
                    ret[label] = value.id_name.split('_').slice(1).join('_')
                }
            }

            return ret
        }
    },
    methods: {
        propertyLabel(prop) {
            let label = prop.split('_').slice(-1).join('') // strip type prefix
            label = label.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase() )
            return label
        },
        propertyValue(prop) {
            let value = this.annotation.properties[prop] ?? null
            return value
        },
        propertyClass(prop) {
            return this.propertyClasses[prop] ?? [];
        },
        generateUrl(type, filter) {
            if (type in this.urls){
                return  (value) => {
                    let filters = [];
                    if (/^(typography)|(orthography)_.*$/.test(filter)){
                        filters.push(qs.stringify( { filters: {["annotation_type"]: this.annotation.type} } ) )
                    }
                    if (/^gtsa_subtype$/.test(filter)){
                        filters.push(qs.stringify( { filters: {["gtsa_type"]: this.annotation.properties.gtsa_type.id} } ) );
                    }
                    if (/^ltsa_subtype$/.test(filter)){
                        filters.push(qs.stringify( { filters: {["gtsa_type"]: this.annotation.properties.ltsa_type.id} } ) );
                    }
                    filters.push( qs.stringify( { filters: {[filter]: value.id} } ) )
                    return this.urls[type] + '?' + filters.join("&");
                }
            }
            return null
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