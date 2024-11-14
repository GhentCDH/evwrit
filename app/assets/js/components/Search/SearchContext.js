import Vue from 'vue'
import qs from "qs";
import { merge as _merge } from "lodash";

export default {
    data() {
        return {
            context: {},
            defaultContext: {
                params: {},
                focus: null,
                searchIndex: null,
                searchSessionHash: null
            },
        }
    },
    methods: {
        /**
         *
         * @param obj
         */
        initContext(data) {
            this.context = _merge({}, this.defaultContext, data)
        },
        // init context from url parameter or window.location
        initContextFromUrl() {
            let context = {}
            try {
                let hash = window.location.hash.substring(1);
                context = JSON.parse(window.atob(hash))
            } catch (e) {
            }
            this.context = _merge({}, this.defaultContext, context)
        },
        getContextHash(data) {
            return window.btoa(JSON.stringify(data ? data : this.context));
        },
        isValidContext() {
            return Object.keys(this.context).length !== 0
        },
        navigateToSearchResult(){
            window.location.href = this.$cookies.get("prev_url");
        }
    },
}
