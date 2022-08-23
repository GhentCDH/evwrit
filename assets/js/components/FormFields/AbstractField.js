import wNumb from 'wnumb'

import Articles from 'articles'


const RANGE_MIN_INVALID = -1
const RANGE_MAX_INVALID = 10000

export default {
    methods: {
        createSelect(label, extra = null, extraSelectOptions = null) {
            let result = {
                type: 'multiselectClear',
                label: label,
                labelClasses: 'control-label',
                placeholder: 'Loading',
                // lowercase first letter + remove spaces
                model: label.charAt(0).toLowerCase() + label.slice(1).replace(/[ ]/g, ''),
                // Values will be loaded using a watcher or Ajax request
                values: [],
                selectOptions: {
                    customLabel: ({id, name}) => {
                        return name
                    },
                    showLabels: false,
                    loading: true,
                    trackBy: 'id',
                },
                // Will be enabled by enableField
                disabled: true,
                anyKey: -2,
                allowAny: true,
                noneKey: -1,
                allowNone: true
            }
            if (extra != null) {
                for (let key of Object.keys(extra)) {
                    result[key] = extra[key]
                }
            }
            if (extraSelectOptions != null) {
                for (let key of Object.keys(extraSelectOptions)) {
                    result['selectOptions'][key] = extraSelectOptions[key]
                }
            }
            return result
        },
        createMultiSelect(label, extra = null, extraSelectOptions) {
            let result = this.createSelect(label, extra, extraSelectOptions)
            result.selectOptions.multiple = true;
            result.selectOptions.closeOnSelect = false;
            return result;
        },
        createRangeSlider(model, label, min, max, step, extra = null) {
            let result = {
                type: "customNoUiSlider",
                styleClasses: "field-noUiSlider",
                label: label,
                model: model,
                min: min,
                max: max,
                noUiSliderOptions: {
                    connect: true,
                    range: {
                        'min': [RANGE_MIN_INVALID,1],
                        '10%': [min,step],
                        '90%': [max,RANGE_MAX_INVALID],
                        'max': [RANGE_MAX_INVALID]
                    },
                    start: [-1, 10000],
                    tooltips: { to: this.formatSliderToolTip },
                }
            }

            return result;
        },
        createOperators(model, extra, allowedOperators = [])
        {
            let result = {
                type: "checkboxes",
                styleClasses: "field-inline-options field-checkboxes-labels-only collapsible collapsed",
                label: 'options',
                model: model,
                parentModel: model.replace('_op',''),
                values: [
                    { name: "OR", value: "or", toggleGroup: "and_or", disabled: this.operatorIsDisabled },
                    { name: "AND", value: "and", toggleGroup: "and_or", disabled: this.operatorIsDisabled },
                    { name: "NOT", value: "not", disabled: this.operatorIsDisabled },
                    { name: "ONLY", value: "only", disabled: this.operatorIsDisabled },
                ]
            }
            if (extra != null) {
                for (let key of Object.keys(extra)) {
                    result[key] = extra[key]
                }
            }
            if (allowedOperators.length) {
                result.values = result.values.filter(item => allowedOperators.includes(item.value))
            }

            return result;
        },
        operatorIsDisabled(model, schema, item) {
            let parentValues = model[schema.parentModel] === undefined ? [] : model[schema.parentModel]
            let parentCount = parentValues.length;
            let globalKeys = [ model[schema.parentModel]?.noneKey ?? -1, model[schema.parentModel]?.anyKey ?? -2 ]

            // any/none selected? disable all
            if ( parentValues.length === 1 && globalKeys.includes(parentValues[0].id) ) {
                return true
            }

            if ( ['and', 'or'].includes(item.value) ) {
                if ( parentCount < 2 ) {
                    return true
                }
            }
            if ( ['not', 'only'].includes(item.value) ) {
                if ( parentCount < 1 ) {
                    return true
                }
            }

            return false
        },
        formatSliderToolTip(value) {
            if ( value > -1 && value < 10000 ) {
                return wNumb({decimals: 0}).to(value)
            } else {
                return 'off';
            }
        },
        disableField(field, model = null) {
            if (model == null) {
                model = this.model
            }
            field.disabled = true
            field.placeholder = 'Loading'
            field.selectOptions.loading = true
            field.values = []
        },
        dependencyField(field, model = null) {
            if (model == null) {
                model = this.model
            }

            // get everything after last '.'
            let modelName = field.model.split('.').pop()

            let label = field.dependencyName ?? this.fields[field.dependency].label.toLowerCase()

            delete model[modelName]
            field.disabled = true
            field.selectOptions.loading = false
            field.placeholder = 'Please select ' + Articles.articlize(label) + ' first'
            // set dependency state
            field.styleClasses = [...new Set(field?.styleClasses?.split(' ') ?? []).add('field--dependency-missing')].join(' ')
        },
        enableField(field, model = null, search = false) {
            if (model == null) {
                model = this.model
            }
            if (field.values.length === 0) {
                return this.noValuesField(field, model, search)
            }

            // get everything after last '.'
            let modelName = field.model.split('.').pop()

            // only keep current value(s) if it is in the list of possible values
            if (model[modelName] != null) {
                if (Array.isArray(model[modelName])) {
                    let newValues = []
                    for (let index of model[modelName].keys()) {
                        if ((field.values.filter(v => v.id === model[modelName][index].id)).length !== 0) {
                            newValues.push(model[modelName][index])
                        }
                    }
                    model[modelName] = newValues
                }
                else if ((field.values.filter(v => v.id === model[modelName].id)).length === 0) {
                    model[modelName] = null
                }
            }

            field.selectOptions.loading = false
            field.disabled = field.originalDisabled == null ? false : field.originalDisabled;
            let label = field.label.toLowerCase()
            field.placeholder = 'Select ' + Articles.articlize(label)

            // remove dependency state
            let classes = new Set(field?.styleClasses?.split(' ') ?? [])
            classes.delete('field--dependency-missing')
            field.styleClasses = [... classes].join(' ')
        },
        noValuesField(field, model = null, search = false) {
            if (model == null) {
                model = this.model
            }

            // Delete value if not on the search page
            if (!search) {
                // get everything after last '.'
                let modelName = field.model.split('.').pop()
                delete model[modelName]
            }

            field.disabled = true
            field.selectOptions.loading = false
            field.placeholder = 'No ' + field.label.toLowerCase() + ' available'
        },
        removeGreekAccents(input) {
            let encoded = encodeURIComponent(input.normalize('NFD'));
            let stripped = encoded.replace(/%C[^EF]%[0-9A-F]{2}/gi, '');
            return decodeURIComponent(stripped).toLocaleLowerCase();
        },
    },
    RANGE_MIN_INVALID: RANGE_MIN_INVALID,
    RANGE_MAX_INVALID: RANGE_MAX_INVALID,
}
