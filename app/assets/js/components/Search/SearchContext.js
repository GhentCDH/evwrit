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
                searchSessionHash: null,
                prev_url: null,
                annotations: null,
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
                context = this.$cookies.get(hash);
            } catch (e) {
            }
            this.context = _merge({}, this.defaultContext, context)
        },
        getContextHash(data) {
            let shortHash = window.btoa(Date.now().toString());
            this.$cookies.set(shortHash, data ? data : this.context, "1d")
            return shortHash
        },
        isValidContext() {
            return Object.keys(this.context).length !== 0
        },
        navigateToSearchResult(){
            try {
                window.location.href = this.context.prev_url;
            } catch(e){
                console.log(e)
            }
        },
    },
}
