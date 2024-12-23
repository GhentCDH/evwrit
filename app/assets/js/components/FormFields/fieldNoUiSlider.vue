<template>
	<div :disabled="disabled" :class="{ 'contain-pips': containPips, 'contain-tooltip': containTooltip }"></div>
</template>

<script>
import { abstractField } from 'vue-form-generator'
import { isArray, defaults } from "lodash";

export default {
	mixins: [abstractField],

	data() {
		return {
			slider: null
		};
	},

	watch: {
		value: function() {
            // console.log("change")
			if (window.noUiSlider && this.slider && this.slider.noUiSlider) {
                this.slider.noUiSlider.set(this.value);
			}
		},
        min: function(val) {
            this.slider.noUiSlider.updateOptions({
                range: {
                    'min': [this.schema.minInvalid,val-this.schema.minInvalid],
                    '10%': [val,this.schema.step],
                    '90%': [this.max,this.schema.maxInvalid],
                    'max': [this.schema.maxInvalid]
                }
            });
        },
        max: function(val) {
            this.slider.noUiSlider.updateOptions({
                range: {
                    'min': [this.schema.minInvalid,this.min-this.schema.minInvalid],
                    '10%': [this.min,this.schema.step],
                    '90%': [val,this.schema.maxInvalid],
                    'max': [this.schema.maxInvalid]
                }
            });
        }

	},

	computed: {
		containPips() {
			return this.schema.noUiSliderOptions && typeof this.schema.noUiSliderOptions.pips !== "undefined";
		},
		containTooltip() {
			return this.schema.noUiSliderOptions && this.schema.noUiSliderOptions.tooltips;
		},
        min() {
            return this.schema.min;
        },
        max() {
            return this.schema.max;
        }
	},

	methods: {
		onChange(value) {
			if (isArray(value)) {
				// Array (range)
				this.value = [parseFloat(value[0]), parseFloat(value[1])];
			} else {
				// Single value
				this.value = parseFloat(value);
			}
            // console.log(value)
		},
		formatValueToField(value) {
			if (this.slider !== null && typeof this.slider.noUiSlider !== "undefined") {
				this.slider.noUiSlider.set(value);
			}
		},
		formatValueToModel(val) {
			if (typeof this.slider.noUiSlider !== "undefined") {
				if (val instanceof Array) {
					return [Number(val[0]), Number(val[1])];
				} else {
					return Number(val);
				}
			}
		},
		getStartValue() {
			if (this.value != null) {
				return this.value;
			} else {
				if (typeof this.schema.noUiSliderOptions !== "undefined" && this.schema.noUiSliderOptions.double) {
					return [this.schema.min, this.schema.min];
				} else {
					return this.schema.min;
				}
			}
		}
	},

	mounted() {
		this.$nextTick(() => {
			if (window.noUiSlider) {
				this.slider = this.$el;
				window.noUiSlider.create(
					this.slider,
					defaults(this.schema.noUiSliderOptions || {}, {
						start: this.getStartValue(),
						range: {
							min: this.schema.min,
							max: this.schema.max
						}
					})
				);
				this.slider.noUiSlider.on("change", this.onChange.bind(this));
			} else {
				console.warn(
					"noUiSlider is missing. Please download from https://github.com/leongersen/noUiSlider and load the script and CSS in the HTML head section!"
				);
			}
		});
	},

	beforeDestroy() {
		if (this.slider) this.slider.noUiSlider.off("change");
	}
};
</script>

<style lang="scss">
.vue-form-generator .field-noUiSlider {
	.field-wrap {
		display: block;
	}
	.contain-pips {
		margin-bottom: 30px;
	}
	.contain-tooltip {
		margin-top: 30px;
	}
	.noUi-vertical {
		height: 200px;
		margin: 10px 0;
	}
}
</style>
