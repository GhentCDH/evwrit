import Vue from 'vue'
import qs from "qs";

export default {
    methods: {
        onData(data) {
            this.aggregation = data.aggregation
            // todo: ditch .data?
            this.data.search = data.search
            this.data.filters = data.filters
            this.data.count = data.count

            console.log(data.count)
        },
        getUrl(route) {
            return this.urls[route] ?? ''
        },
        getTextUrl(id, index) {
            return this.urls['text_get_single'].replace('text_id', id) + '?context=' + this.getContextHash(index)
        },
        getContextHash(index) {
            let params = qs.parse(window.location.href.split('?',2)[1], { plainObjects: true }) ?? [];
            delete params.limit;
            delete params.page;
            params.orderBy = params?.orderBy ?? this.tableOptions.orderBy?.column ?? null;
            params.ascending = params?.ascending ?? 1;

            let context = {
                urls: {
                    paginate: this.getUrl('paginate'),
                    search: this.getUrl('search'),
                },
                params: params,
                index: (this.data.search.page - 1) * this.data.search.limit + index,
                count: this.data.count
            }

            return window.btoa(JSON.stringify(context));
        },
    },
}
