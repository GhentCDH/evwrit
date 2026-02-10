import Vue from 'vue'

/* import noUiSlider */
import 'nouislider/dist/nouislider.css'
import * as noUiSlider from 'nouislider/dist/nouislider.js'
window.noUiSlider = noUiSlider

import TextSearchApp from '../components/Search/TextSearchApp.vue'

new Vue({
    el: '#text-search-app',
    components: {
        TextSearchApp
    }
})
