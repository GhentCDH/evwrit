import Vue from 'vue'
import VueCookies from 'vue-cookies'

Vue.use(VueCookies)

export default function(cookieName) {
    return {
        data() {
            return {
                groupIsOpen: [],
                cookieName: cookieName
            }
        },
        watch: {
            groupIsOpen(newVal, oldVal) {
                this.schema.groups.forEach(function (group, index) {
                    group.styleClasses = (newVal[index] !== undefined && newVal[index]) ? 'collapsible' : 'collapsible collapsed'
                })
                this.setCookie(this.cookieName, this.groupIsOpen)
            },
        },
        methods: {
            collapseGroup(e) {
                const group = e.target.parentElement;
                // get element index
                let index = Array.from(group.parentNode.children).indexOf(group)
                Vue.set(this.groupIsOpen, index, this.groupIsOpen[index] !== undefined ? !this.groupIsOpen[index] : true)
            },
            setCookie(name, value) {
                this.$cookies.set(name,JSON.stringify(value),'30d')
            },
            getCookie(name, defaultValue) {
                try {
                    let ret
                    ret = JSON.parse(this.$cookies.get(name))
                    return ret
                } catch(error) {
                    return defaultValue
                }
            },
        },
        mounted() {
            // make legends clickable
            const collapsableLegends = this.$el.querySelectorAll('.vue-form-generator .collapsible legend');
            collapsableLegends.forEach(legend => legend.onclick = this.collapseGroup);
        },
        created() {
            if ( !this.$cookies.isKey(this.cookieName) )
                this.setCookie(this.cookieName, [])
            else {
                this.groupIsOpen = this.getCookie(this.cookieName,[])
            }
        }
    }
}
