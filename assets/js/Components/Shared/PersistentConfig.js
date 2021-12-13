import Vue from 'vue'
import VueCookies from 'vue-cookies'

Vue.use(VueCookies)

export default function(cookieName) {
    return {
        data() {
            return {
                configCookieName: cookieName,
                config: {},
            }
        },
        watch: {
            config: {
                handler: function (val, oldVal) {
                    this.setCookie(this.configCookieName, val)
                    this.$emit('config-changed')
                },
                deep: true
            },
        },
        methods: {
            setCookie(name, value) {
                this.$cookies.set(name,value,'30d')
            },
            getCookie(name, defaultValue) {
                try {
                    let ret
                    ret = this.$cookies.get(name)
                    if (ret) {
                        return ret
                    }
                } catch(error) {
                    return defaultValue
                }
                return defaultValue;
            },
        },
        mounted() {
        },
        created() {
            this.config = this.defaultConfig;
            if ( !this.$cookies.isKey(this.configCookieName) )
                this.setCookie(this.configCookieName, this.config)
            else {
                this.config = this.getCookie(this.configCookieName,this.defaultConfig)
            }
        }
    }
}
