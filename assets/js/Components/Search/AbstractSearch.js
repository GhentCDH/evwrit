window.axios = require('axios')

import qs from 'qs'

import Vue from 'vue'
import VueFormGenerator from 'vue-form-generator'
import VueMultiselect from 'vue-multiselect'
import VueTables from 'vue-tables-2'
import * as uiv from 'uiv'

import fieldMultiselectClear from '../FormFields/fieldMultiselectClear'

Vue.use(uiv)
Vue.use(VueFormGenerator)
Vue.use(VueTables.ServerTable)

Vue.component('multiselect', VueMultiselect)
Vue.component('fieldMultiselectClear', fieldMultiselectClear)

const YEAR_MIN = 1
const YEAR_MAX = (new Date()).getFullYear()

export default {
    props: {
        initUrls: {
            type: String,
            default: '',
        },
        initData: {
            type: String,
            default: '',
        },
    },
    data () {
        return {
            urls: JSON.parse(this.initUrls),
            data: JSON.parse(this.initData),
            model: {},
            originalModel: {},
            formOptions: {
                validateAfterLoad: true,
                validateAfterChanged: true,
                validationErrorClass: "has-error",
                validationSuccessClass: "success"
            },
            openRequests: 0,
            tableCancel: null,
            actualRequest: false,
            initialized: false,
            historyRequest: false,
            // prevent the creation of a browser history item
            noHistory: false,
            // used to set timeout on free input fields
            lastChangedField: '',
            // used to only send requests after timeout when inputting free input fields
            inputCancel: null,
            // Remove requesting the same data that is already displayed
            oldFilterValues: {},
            aggregation: {},
            lastOrder: null,
            countRecords: '',
        }
    },
    computed: {
        fields() {
            let res = {};
            if (this.schema && this.schema.fields) {
                this.schema.fields.forEach( field => {
                    if (!this.multiple || field.multi === true)
                        res[field.model] = field;
                });
            }
            if (this.schema && this.schema.groups) {
                this.schema.groups.forEach( group => {
                    if (group.fields) {
                        group.fields.forEach(field => {
                            if (!this.multiple || field.multi === true)
                                res[field.model] = field;
                        });
                    }
                });
            }
            return res;
        },
        showReset() {
            for (let key in this.model) {
                if (this.model.hasOwnProperty(key)) {
                    if (
                        (this.model[key] != null && (!(key in this.originalModel) || JSON.stringify(this.model[key]) !== JSON.stringify(this.originalModel[key])))
                        || (this.model[key] == null && (key in this.originalModel) && this.originalModel[key] != null)
                    ) {
                        return true
                    }
                }
            }
            return false
        },
        histWeights: function () {
            if (this.aggregation.histWeights == null) {
                return []
            }
            let keys = Object.keys(this.aggregation.histWeights);
            let numKeys = keys.map(Number);
            function sortNumber(a,b) {
                return a - b;
            }
            numKeys.sort(sortNumber);
            let histWeights = [];
            for(let key of numKeys)
            {
                histWeights.push([key, this.aggregation.histWeights[key.toString()]]);
            }
            return histWeights;
        },
        maxHeight: function () {
            if (this.aggregation.histWeights == null) {
                return 0
            }
            let maxHeight = null;
            let values = Object.values(this.aggregation.histWeights);
            for(let value of values)
            {
                if(maxHeight == null || maxHeight < value)
                {
                    maxHeight = value;
                }
            }
            return maxHeight;
        },
        minHist: function() {
            if (this.histWeights.length === 0) {
                return 0;
            }
            return this.histWeights[0][0];
        },
        maxHist: function() {
            if (this.histWeights.length === 0) {
                return 0;
            }
            return this.histWeights[this.histWeights.length - 1][0];
        },
        querystring: function() {
            return qs.stringify({filters: this.oldFilterValues});
        },
        dateRange: function () {
            if (this.model.dateRange != null) {
                return  {
                    value: [this.model.dateRange[0], this.model.dateRange[1]],
                    min: this.minHist,
                    max: this.maxHist,
                }
            }
            return {
                value: [this.minHist, this.maxHist],
                min: this.minHist,
                max: this.maxHist,
            }
        },
    },
    mounted () {
        this.originalModel = JSON.parse(JSON.stringify(this.model))
        window.onpopstate = ((event) => {this.popHistory(event)})
        this.updateCountRecords()
    },
    methods: {
        constructFilterValues() {
            let result = {}
            if (this.model != null) {
                for (let fieldName of Object.keys(this.model)) {
                    if (this.fields[fieldName] != null && this.fields[fieldName].type === 'multiselectClear') {
                        if (this.model[fieldName] != null) {
                            if (Array.isArray(this.model[fieldName]))
                            {
                                var ids = []
                                for (let value of this.model[fieldName]) {
                                    ids.push(value['id'])
                                }
                                result[fieldName] = ids

                            }
                            else
                            {
                                result[fieldName] = this.model[fieldName]['id']
                            }

                        }
                    }
                    else {
                        if(fieldName === 'dateRange')
                        {
                            if (this.model.dateRange[0] !== this.minHist || this.model.dateRange[1] !== this.maxHist) {
                                result[fieldName] = this.model[fieldName]
                            }
                        }
                        else
                        {
                            result[fieldName] = this.model[fieldName]
                        }
                    }
                }
            }
            return result
        },
        modelUpdated(value, fieldName) {
            this.lastChangedField = fieldName
        },
        onValidated(isValid, errors) {
            // do nothin but cancelling requests if invalid
            if (!isValid) {
                if (this.inputCancel !== null) {
                    window.clearTimeout(this.inputCancel)
                    this.inputCancel = null
                }
                return
            }

            // Cancel timeouts caused by input requests not long ago
            if (this.inputCancel != null) {
                window.clearTimeout(this.inputCancel)
                this.inputCancel = null
            }

            // Send requests to update filters and result table
            // Add a delay to requests originated from input field changes to limit the number of requests
            let timeoutValue = 0
            if (
                this.lastChangedField !== ''
                && (
                    this.lastChangedField === 'dateInput'
                    || (
                        this.fields[this.lastChangedField]
                        && this.fields[this.lastChangedField].type === 'input'
                    )
                )
            ) {
                timeoutValue = 1000
            }

            // Remove column ordering if text or comment is searched, reset when no value is provided
            // Do not refresh twice
            if (this.lastChangedField == 'text' || this.lastChangedField == 'comment') {
                this.actualRequest = false
                if (this.model[this.lastChangedField] == null || this.model[this.lastChangedField == '']) {
                    if (this.lastOrder == null) {
                        this.$refs.resultTable.setOrder(this.defaultOrdering, true)
                    }
                    else {
                        let asc = (this.lastOrder.hasOwnProperty('ascending') && this.lastOrder['ascending'])
                        this.$refs.resultTable.setOrder(this.lastOrder.column, asc)
                    }
                }
                else {
                    this.lastOrder = JSON.parse(JSON.stringify(this.$refs.resultTable.orderBy))
                    this.$refs.resultTable.setOrder(null)
                }
            }

            // Don't get new data if last changed field is text_type and text is null or empty
            // else: remove column ordering
            if (this.lastChangedField == 'text_type') {
                if (this.model.text == null || this.model.text == '') {
                    this.actualRequest = false
                }
                else {
                    this.actualRequest = false
                    this.$refs.resultTable.setOrder(null)
                    this.actualRequest = true
                }
            }
            else {
                this.actualRequest = true
            }

            // Don't get new data if history is being popped
            if (this.historyRequest) {
                this.actualRequest = false
            }


            this.inputCancel = window.setTimeout(() => {
                this.inputCancel = null
                let filterValues = this.constructFilterValues()
                // only send request if the filters have changed
                // filters are always in the same order, so we can compare serialization
                if (JSON.stringify(filterValues) !== JSON.stringify(this.oldFilterValues)) {
                    this.oldFilterValues = JSON.parse(JSON.stringify(filterValues))
                    VueTables.Event.$emit('vue-tables.filter::filters', filterValues)
                }
            }, timeoutValue)
        },
        sortByName(a, b) {
            // Place 'any' and 'none' filters above
            if((a.name === 'none' || a.name === 'all') && (b.name != 'all' && b.name != 'none')) {
                return -1
            }
            if((a.name != 'all' && a.name != 'none') && (b.name === 'all' || b.name === 'none')) {
                return 1
            }

            // Place true before false
            if (a.name === 'false' && b.name === 'true') {
                return 1
            }
            if (a.name === 'true' && b.name === 'false') {
                return -1
            }
            // Default
            if (a.name < b.name) {
                return -1
            }
            if (a.name > b.name) {
                return 1
            }
            return 0
        },
        resetAllFilters() {
            this.model = JSON.parse(JSON.stringify(this.originalModel))
            this.onValidated(true)
        },
        onData(data) {
            this.aggregation = data.aggregation
            if (this.model.dateRange != null) {
                this.originalModel.dateRange = [
                    this.minHist,
                    this.maxHist,
                ]
            }
        },
        onLoaded(data) {
            // Update model and ordering if not initialized or history request
            if (!this.initialized) {
                this.init(true)
                this.initialized = true
            }
            if (this.historyRequest) {
                this.init(this.historyRequest === 'init')
                this.historyRequest = false
            }

            // Update aggregation fields
            for (let fieldName of Object.keys(this.fields)) {
                let field = this.fields[fieldName]
                if (field.type === 'multiselectClear') {
                    let values = this.aggregation[fieldName] == null ? [] : this.aggregation[fieldName].sort(this.sortByName)
                    field.values = values
                    if (field.dependency != null && this.model[field.dependency] == null) {
                        this.dependencyField(field)
                    }
                    else {
                        this.enableField(field)
                    }
                }
            }

            // Update number of records text
            this.updateCountRecords()

            this.openRequests--
        },
        pushHistory(data) {
            history.pushState(data, document.title, document.location.href.split('?')[0] + '?' + qs.stringify(data))
        },
        popHistory(event) {
            // set querystring
            if (window.location.href.split('?', 2).length > 1) {
                this.historyRequest = window.location.href.split('?', 2)[1]
            }
            else {
                this.historyRequest = 'init'
            }
            this.$refs.resultTable.refresh()
        },
        init(initial) {
            // set model
            let params = qs.parse(window.location.href.split('?', 2)[1])
            let model = JSON.parse(JSON.stringify(this.originalModel))
            if (params.hasOwnProperty('filters')) {
                Object.keys(params['filters']).forEach((key) => {
                    if (this.fields.hasOwnProperty(key)) {
                        if (this.fields[key].type === 'multiselectClear' && this.aggregation[key] != null) {
                            if (Array.isArray(params['filters'][key])) {
                                model[key] = this.aggregation[key].filter(v => params['filters'][key].includes(String(v.id)))
                            }
                            else {
                                model[key] = this.aggregation[key].filter(v => String(v.id) === params['filters'][key])[0]
                            }
                        }
                        else {
                            model[key] = params['filters'][key]
                        }
                    }
                    if (key === 'dateRange') {
                        model[key] = params['filters'][key]
                    }
                }, this)
            }
            this.model = model

            // set oldFilterValues
            this.oldFilterValues = this.constructFilterValues()

            // set table page
            if (params.hasOwnProperty('page')) {
                this.actualRequest = false
                this.$refs.resultTable.setPage(params['page'])
            }
            // set table ordering
            this.actualRequest = false
            if (params.hasOwnProperty('orderBy')) {
                let asc = (params.hasOwnProperty('ascending') && params['ascending'])
                this.$refs.resultTable.setOrder(params['orderBy'], asc)
            }
            else if (
                params.hasOwnProperty('filters')
                && (
                    (params['filters'].hasOwnProperty('text') && params['filters']['text'] != null && params['filters']['text'] != '')
                    || (params['filters'].hasOwnProperty('comment') && params['filters']['comment'] != null && params['filters']['comment'] != '')
                )
            ) {
                this.$refs.resultTable.setOrder(null)
            }
            else {
                this.$refs.resultTable.setOrder(this.defaultOrdering, true)
            }
        },
        updateCountRecords() {
            let table = this.$refs.resultTable
            if (!table.count) {
                this.countRecords = ''
                return
            }
            let perPage = parseInt(table.limit)

            let from = ((table.Page-1) * perPage) + 1
            let to = table.Page==table.totalPages?table.count:from + perPage - 1

            let parts = table.opts.texts.count.split('|')
            let i = Math.min(table.count==1?2:table.totalPages==1?1:0, parts.length-1)

            this.countRecords = parts[i].replace('{count}', table.count)
                .replace('{from}', from)
                .replace('{to}', to)
        },
        isLoginError(error) {
            return error.message === 'Network Error'
        },
        formatDate (date) {
            if(date != null){
                if(date < 0) {
                    return (-1) * date + " BC"
                }
                else {
                    return date + " AD"
                }
            }
        },
        sliderChange () {
            this.model.dateRange = this.dateRange.value;
            this.onValidated(true);
        },
        dateInputChange() {
            if (
                this.model.dateRange[0] >= this.minHist
                && this.model.dateRange[1] >= this.minHist
                && this.model.dateRange[0] <= this.maxHist
                && this.model.dateRange[1] <= this.maxHist
                && this.model.dateRange[0] <= this.model.dateRange[1]
            ) {
                this.lastChangedField = 'dateInput'
                this.onValidated(true);
            }
        },
    },
    requestFunction (data) {
        // Remove unused parameters
        delete data['query']
        delete data['byColumn']
        if (!data.hasOwnProperty('orderBy')) {
            delete data['ascending']
        }
        // Add filter values if necessary
        data['filters'] = this.$parent.constructFilterValues()
        if (data['filters'] == null || data['filters'] == '') {
            delete data['filters']
        }
        this.$parent.openRequests++
        if (!this.$parent.initialized) {
            return new Promise((resolve, reject) => {
                this.$emit('data', this.$parent.data)
                resolve({
                    data : {
                        data: this.$parent.data.data,
                        count: this.$parent.data.count
                    }
                })
            })
        }
        if (!this.$parent.actualRequest) {
            return new Promise((resolve, reject) => {
                resolve({
                    data : {
                        data: this.data,
                        count: this.count
                    }
                })
            })
        }
        if (this.$parent.historyRequest) {
            if (this.$parent.openRequests > 1 && this.$parent.tableCancel != null) {
                this.$parent.tableCancel('Operation canceled by newer request')
            }
            let url = this.url
            if (this.$parent.historyRequest !== 'init') {
                url += '?' + this.$parent.historyRequest
            }
            return axios.get(url, {
                cancelToken: new axios.CancelToken((c) => {this.$parent.tableCancel = c})
            })
                .then( (response) => {
                    this.$emit('data', response.data)
                    return response
                })
                .catch(function (error) {
                    this.$parent.historyRequest = false
                    this.$parent.openRequests--
                    if (axios.isCancel(error)) {
                        // Return the current data if the request is cancelled
                        return {
                            data : {
                                data: this.data,
                                count: this.count
                            }
                        }
                    }
                    this.dispatch('error', error)
                }.bind(this))
        }
        if (!this.$parent.noHistory) {
            this.$parent.pushHistory(data)
        } else {
            this.$parent.noHistory = false;
        }

        if (this.$parent.openRequests > 1 && this.$parent.tableCancel != null) {
            this.$parent.tableCancel('Operation canceled by newer request')
        }
        return axios.get(this.url, {
            params: data,
            paramsSerializer: qs.stringify,
            cancelToken: new axios.CancelToken((c) => {this.$parent.tableCancel = c})
        })
            .then( (response) => {
                this.$emit('data', response.data)
                return response
            })
            .catch(function (error) {
                if (axios.isCancel(error)) {
                    // Return the current data if the request is cancelled
                    return {
                        data : {
                            data: this.data,
                            count: this.count
                        }
                    }
                }
                this.dispatch('error', error)
            }.bind(this))
    },
    YEAR_MIN: YEAR_MIN,
    YEAR_MAX: YEAR_MAX,
}
