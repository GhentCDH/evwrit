import Vue from 'vue'
import qs from "qs";
import SearchSession from "./SearchSession";
import SearchContext from "./SearchContext";
import CollapsibleGroups from "./CollapsibleGroups";

export default {
    mixins: [
        SearchSession,
        SearchContext,
        CollapsibleGroups,
    ],
    methods: {
        onData(data) {
            // update search session
            let params = this.getSearchParams();
            this.updateSearchSession({
                params: params,
                count: data.count
            })

            // update local data
            this.aggregation = data.aggregation
            // todo: ditch .data?
            this.data.search = data.search
            this.data.filters = data.filters
            this.data.count = data.count
        },
        getUrl(route) {
            return this.urls[route] ?? ''
        },
        getTextUrl(id, index) {
            let context = {
                params: this.data.filters,
                searchIndex: (this.data.search.page - 1) * this.data.search.limit + index, // rely on data or params?
                searchSessionHash: this.getSearchSessionHash()
            }
            return this.urls['text_get_single'].replace('text_id', id) + '#' + this.getContextHash(context)
        },
        getSearchParams() {
            let params = qs.parse(window.location.href.split('?',2)[1], { plainObjects: true }) ?? [];
            params.orderBy = params.orderBy ?? this.tableOptions.orderBy?.column ?? null;
            params.ascending = params.ascending ?? 1;
            params.page = params.page ?? 1;
            params.limit = params.limit ?? this.tableOptions.perPage ?? 25;

            return params;
        },
    },
    created() {
        this.initSearchSession({
            urls: {
                paginate: this.getUrl('paginate'),
            },
            count: this.data.count,
            params: this.getSearchParams()
        })
    }
}
