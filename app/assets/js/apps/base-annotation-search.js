import Vue from 'vue'

/* import noUiSlider */
import 'nouislider/dist/nouislider.css'
import * as noUiSlider from 'nouislider/dist/nouislider.js'
window.noUiSlider = noUiSlider

import BaseAnnotationSearchApp from '../components/Search/BaseAnnotationSearchApp.vue'

new Vue({
    el: '#base-annotation-search-app',
    components: {
        BaseAnnotationSearchApp
    }
})
