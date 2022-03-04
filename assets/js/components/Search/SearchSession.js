import Vue from 'vue'
import qs from "qs";
import _merge from "lodash.merge";

export default {
    data() {
        return {
            defaultSearchSession: {
                hash: null,
                urls: {
                },
                count: 0,
                params: {
                }
            },
        }
    },
    methods: {
        initSearchSession(data) {
            let sessionData = _merge({}, this.defaultSearchSession, data)
            sessionData.hash = Date.now();
            window.sessionStorage.setItem('search_session', JSON.stringify(sessionData));
        },
        updateSearchSession(data) {
            let sessionData = this.getSearchSession();
            sessionData.params = {} // clear params
            sessionData = _merge({}, sessionData, data)
            sessionData.hash = Date.now();
            window.sessionStorage.setItem('search_session', JSON.stringify(sessionData));
        },
        getSearchSession(sessionHash) {
            try {
                let sessionData = JSON.parse(window.sessionStorage.getItem('search_session'));
                if (sessionHash) {
                    return (sessionData.hash == sessionHash ? sessionData : null)
                } else {
                    return sessionData;
                }
            } catch(e) {
                return null;
            }
        },
        getSearchSessionHash() {
            return this.getSearchSession()?.hash ?? null
        },
    },
}
