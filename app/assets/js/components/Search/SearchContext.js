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
                context = JSON.parse(localStorage.getItem(hash));
            } catch (e) {
            }
            this.context = _merge({}, this.defaultContext, context)
        },
        getContextHash(data) {
            let hash = window.btoa(JSON.stringify(data ? data : this.context));
            let shortHash = localStorage.getItem(hash);
            if (!shortHash){
                shortHash = window.btoa(Date.now().toString());
                localStorage.setItem(hash, shortHash);
                localStorage.setItem(shortHash, JSON.stringify(data ? data : this.context))
            }
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
