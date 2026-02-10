import Vue from 'vue'

/* import noUiSlider */
import 'nouislider/dist/nouislider.css'
import * as noUiSlider from 'nouislider/dist/nouislider.js'
window.noUiSlider = noUiSlider

import MaterialitySearchApp from '../components/Search/MaterialitySearchApp.vue'

new Vue({
    el: '#materiality-search-app',
    components: {
        MaterialitySearchApp
    }
})
