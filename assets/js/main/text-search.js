import Vue from 'vue'

/* import noUiSlider */
import 'nouislider/dist/nouislider.css'
import * as noUiSlider from 'nouislider/dist/nouislider.js'
window.noUiSlider = noUiSlider

import TextSearchApp from '../apps/TextSearchApp'

new Vue({
    el: '#text-search-app',
    components: {
        TextSearchApp
    }
})
