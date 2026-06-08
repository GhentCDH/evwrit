<template>
    <div class="labelvalue row" :class="'labelvalue--' + type" v-if="isVisible">
        <div :class="labelClassAttr">
            {{ label }}
        </div>
        <div :class="valueClassAttr">
            <template v-if="value != null">
                <FormatValue
                        v-for="(item, index) in outputValues"
                        :key="index"
                        :type="type"
                        :value="item"
                        :url="isCallable(url) ? url(item) : url"
                        :class="isCallable(valueClass) ? valueClass(item) : valueClass"
                        :locale="locale"
                        :second-locale="secondLocale"
                        v-bind="$attrs"
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
    inheritAttrs: false,
    components: {
        FormatValue
    },
    props: {
        label: {
            type: String,
        },
        value: {
            type: [String, Number, Object, Array]
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
        grid: {
            type: String,
            default: '7|5'
        },
        url: {
            type: [String, Function],
            default: null
        },
        ignoreValue: {
            type: Array,
            default: () => []
        }
    },
    computed: {
        labelGridCols() {
            return this.grid.split('|')[0] ?? 7;
        },
        valueGridCols() {
            return this.grid.split('|')[1] ?? 5;
        },
        labelClassAttr() {
            return ['labelvalue__label', this.inline ? 'labelvalue__label--inline col-xs-' + this.labelGridCols : 'col-xs-12', this.labelClass ?? ''].join(' ')
        },
        valueClassAttr() {
            return ['labelvalue__value', this.inline ? 'labelvalue__value--inline col-xs-' + this.valueGridCols : 'col-xs-12', this.valueClass ?? ''].join(' ')
        },
        outputValues() {
            let values = this.value ? ( Array.isArray(this.value) ? this.value : [ this.value ] ) : ( this.unknown ? [ this.unknown ] : [] )
            switch(this.type) {
                case 'id_name':
                    values = values.filter( (item) => !this.ignoreValue.includes(item?.name) )
                    break
                case 'string':
                    values = values.filter( (value) => !value || !this.ignoreValue.includes(value) )
            }

            return values
        },
        isVisible() {
            return this.outputValues.length || this.$scopedSlots.default
        }
    },
    methods: {
        isCallable(prop) {
            return (prop instanceof Function)
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