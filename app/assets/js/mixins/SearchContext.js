import {merge as _merge} from "lodash";

const MAX_LOCALSTORAGE_CONTEXTS = 100

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
                context = JSON.parse(localStorage.getItem("context"))[hash]["data"]
            } catch (e) {
            }
            this.context = _merge({}, this.defaultContext, context)
        },
        getContextHash() {
            return window.btoa(Date.now().toString())
        },
        saveContextHash(hash, data){
            try {
                if (!localStorage.getItem("context")){
                    let init_context = {
                        "LRU": hash,
                        "MRU": hash,
                    }
                    init_context[hash] = {
                        "data": data ? data : this.context,
                        "next": ""
                    }
                    localStorage.setItem("context", JSON.stringify(init_context))
                } else {
                    let contexts = JSON.parse(localStorage.getItem("context"));
                    contexts[contexts["MRU"]]["next"] = hash;
                    contexts[hash] = {
                        "data": data ? data : this.context,
                        "next": ""
                    }
                    contexts["MRU"] = hash;
                    while (Object.keys(contexts).length > MAX_LOCALSTORAGE_CONTEXTS){
                        let lru = contexts["LRU"]
                        contexts["LRU"] = contexts[lru]["next"]
                        delete contexts[lru]
                    }
                    localStorage.setItem("context", JSON.stringify(contexts))
                }
            } catch (e){
                localStorage.clear();
                this.saveContextHash(hash, data)
            }

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
