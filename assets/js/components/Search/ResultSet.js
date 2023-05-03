import axios from "axios";
import qs from "qs";
import _merge from "lodash.merge";

export default {
    data() {
        return {
            defaultResultSet: {
                params: {
                    filters: {},
                    page: 1, // todo: move to global config
                    limit: 10 // todo: move to global config
                },
                ids: [],
                url: null,
                count: 0
            },
            resultSet: null
        }
    },
    methods: {
        initResultSet(url, params, count) {
            this.resultSet = _merge(
                {},
                this.defaultResultSet,
                {
                    url: url,
                    params: params,
                    count: count
                }
            )

            if (typeof this.resultSet.params.limit === 'string') {
                this.resultSet.params.limit =  Number.parseInt(this.resultSet.params.limit) ?? 10 // todo: move to global config
            }
            if (typeof this.resultSet.params.page === 'string') {
                this.resultSet.params.page =  Number.parseInt(this.resultSet.params.page) ?? 1
            }
            if (typeof this.resultSet.params.count === 'string') {
                this.resultSet.params.count =  Number.parseInt(this.resultSet.params.count) ?? 0
            }

            if ( this.resultSet.url && this.resultSet.params ) {
                this.updateResultSetIndex()
            }
        },
        updateResultSetIndex() {
            let self = this;
            return axios.get(this.resultSet.url + '?' + qs.stringify(this.resultSet.params) ).then( function(response) {
                self.resultSet.ids = response.data;
            });
        },
        async getResultSetIdByIndex(index) {
            if ( !index || index < 1 || index > this.resultSet.count ) return null;

            let limit = this.resultSet.params.limit
            let page = Math.floor((index -1) / limit) + 1

            if ( page !== this.resultSet.params.page ) {
                this.resultSet.params.page = page
                await this.updateResultSetIndex()
            }

            let rsIndex = (index - 1) - (page - 1)*limit
            return this.resultSet.ids[rsIndex]
        }
    },
    created() {
        if ( !this.resultSet ) {
            this.resultSet = this.defaultResultSet
        }
    }
}
