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
            try {
                window.location.href = this.context.prev_url;
            } catch(e){
                console.log(e)
            }
            // try {
            //     const hash = window.location.hash;
            //     const prev_url = this.$cookies.get(`${hash}_prev_url`);
            //     if (prev_url){
            //         this.$cookies.remove(`${hash}_prev_url`);
            //         this.$cookies.remove(`${hash}_search_context_annotations`);
            //         window.location.href = prev_url;
            //     }
            // } catch(e){
            //     console.error(e);
            // }
        },
        updateHashCookie(oldKey, newKey){
            let value = this.$cookies.get(`${oldKey}_prev_url`);
            this.$cookies.remove(`${oldKey}_prev_url`);
            this.$cookies.set(`${newKey}_prev_url`, value);

            value = this.$cookies.get(`${oldKey}_search_context_annotations`);
            this.$cookies.remove(`${oldKey}_search_context_annotations`);
            this.$cookies.set(`${newKey}_search_context_annotations`, value);
        }
    },
}
