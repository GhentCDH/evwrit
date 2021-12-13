<template>
    <div class="labelvalue row" :class="'labelvalue--' + type">
        <div :class="outputLabelClass">
            {{ label }}
        </div>
        <div :class="outputValueClass">
            <template v-if="value != null">
                <FormatValue v-if="Array.isArray(value) && value.length" v-for="(item, index) in value" :key="index" :type="type" :value="item" :url="isCallable(url) ? url(value) : url" />
                <FormatValue v-if="!Array.isArray(value)" :value="value" :type="type" :url="isCallable(url) ? url(value) : url"/>
            </template>
            <span v-else>{{ unknown }}</span>
        </div>
    </div>
</template>

<script>
import FormatValue from "./FormatValue";

export default {
    name: "LabelValue",
    components: {
        FormatValue
    },
    props: {
        label: {
            type: String,
        },
        value: {
            type: String|Number|Object|Array
        },
        unknown: {
            type: String,
            default: 'Unknown'
        },
        inline: {
            type: Boolean,
            default: true
        },
        valueClass: {
            type: String,
            default: null
        },
        labelClass: {
            type: String,
            default: null
        },
        type: {
            type: String,
            default: 'string'
        },
        url: {
            type: String|Function,
            default: null
        },
    },
    computed: {
        outputLabelClass() {
            return ['labelvalue__label', this.inline ? 'labelvalue__label--inline col-xs-5' : 'col-xs-12', this.labelClass ? this.labelClass : ''].join(' ')
        },
        outputValueClass() {
            return ['labelvalue__value', this.inline ? 'labelvalue__value--inline col-xs-7' : 'col-xs-12', this.valueClass ? this.valueClass : ''].join(' ')
        },
    },
    methods: {
        isCallable(prop) {
            if ( prop instanceof Function ) {
                return true
            }
            return false
        }
    }
}
</script>

<style scoped lang="scss">
.labelvalue__label {
  color: #666;
}

.labelvalue__value > span {
  padding: 4px;
  color: black;
  display: inline-block;
}

.labelvalue__value--inline > span {
  padding: 0;
}
</style>