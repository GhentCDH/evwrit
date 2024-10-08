<template>
    <div class="labelvalue row" :class="'labelvalue--' + type" v-if="visible">
        <div :class="outputLabelClass">
            {{ label }}
        </div>
        <div :class="outputValueClass">
            <template v-if="value != null">
                <FormatValue
                        v-if="outputValues && outputValues.length"
                        v-for="(item, index) in outputValues"
                        :type="type"
                        :value="item"
                        :url="isCallable(url) ? url(item) : url"
                        :class="isCallable(valueClass) ? valueClass(item) : valueClass"
                        :locale="locale"
                        :second-locale="secondLocale"
                />
            </template>
            <template v-else-if="$scopedSlots.default">
                <slot></slot>
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
            default: null
        },
        inline: {
            type: Boolean,
            default: true
        },
        valueClass: {
            type: String|Function,
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
        locale: {
            type: String,
            default: null
        },
        secondLocale: {
            type: String,
            default: null
        },
        ignoreValue: {
            type: Array,
            default: () => []
        }
    },
    computed: {
        outputLabelClass() {
            return ['labelvalue__label', this.inline ? 'labelvalue__label--inline col-xs-5' : 'col-xs-12', this.labelClass ? this.labelClass : ''].join(' ')
        },
        outputValueClass() {
            return ['labelvalue__value', this.inline ? 'labelvalue__value--inline col-xs-7' : 'col-xs-12'].join(' ')
        },
        outputValues() {
            let values = this.value ? ( Array.isArray(this.value) ? this.value : [ this.value ] ) : ( this.unknown ? [ this.unknown ] : [] )
            switch(this.type) {
                case 'id_name':
                    values = values.filter( (item) => !this.ignoreValue.includes(item.name) )
                    break
                case 'string':
                    values = values.filter( (value) => !value || !this.ignoreValue.includes(value) )
            }

            return values
        },
        visible() {
            return this.outputValues.length || this.$scopedSlots.default
        }
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
  //padding: 4px;
  //color: black;
  display: inline-block;
}

.labelvalue__value--inline > span {
  //padding: 0;
  margin-right: 0.3em;

  &:last-child {
    margin-right: 0;
  }

}
</style>