import Vue from 'vue'

/* import noUiSlider */
import 'nouislider/dist/nouislider.css'
import * as noUiSlider from 'nouislider/dist/nouislider.js'
window.noUiSlider = noUiSlider

import TextStructureSearchApp from '../apps/TextStructureSearchApp'

new Vue({
    el: '#text-structure-search-app',
    components: {
        TextStructureSearchApp
    }
})
