<template>
    <div class="widget" :class="{closed: !isOpen}">
        <div class="sticky-block" @click="toggleOpen()">
            <div class="title">
                <span class="toggle-open" >
                    <i class="fa fa-angle-down" aria-hidden="true"></i>
                </span>
                <span>{{ title }}
                    <span class="count" v-if="count >= 0">{{ count }}</span>
                </span>
            </div>
        </div>
        <div class="body" :class="{fixed: fixed}">
            <slot></slot>
        </div>
    </div>
</template>

<script>
import VueCookies from 'vue-cookies'

import Vue from 'vue'
Vue.use(VueCookies)

export default {
    name: "Widget",
    components: {
        VueCookies
    },
    props: {
        title: {
            type: String,
            required: true
        },
        count: {
            type: Number,
            required: false
        },
        initFixed: {
            type: Boolean,
            default: false
        },
        initOpen: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            open: this.initOpen,
            fixed: this.initFixed
        }
    },
    computed: {
        isOpen: function() { return this.open }
    },
    methods: {
        toggleOpen: function() {
            this.open = !this.open;
            this.$cookies.set(this.cookieName(),this.open,'30d')
        },
        cookieName() {
            return 'sbw-'+this.title
        },
    },
    created() {
        if ( !this.$cookies.isKey(this.cookieName()) )
            this.$cookies.set(this.cookieName(),this.initOpen,'30d')
        else
            this.open = (this.$cookies.get(this.cookieName()) == "true")
    }
}
</script>

<style scoped lang="scss">
.widget {

  .sticky-block {
  }
  .title {
    text-transform: uppercase;
    font-size: 18px;
    letter-spacing: .1em;
    cursor: pointer;
    padding: 10px 0 5px;

    font-family: "PannoTextMedium", Arial, sans-serif;
    color: #777;

    .toggle-open {
      float: right;
    }

    .count {
      border: 1px solid #aaa;
      padding: 3px 5px;
      border-radius: 5px;
      font-size: 80%;
      color: #aaa;
      position: relative;
      top: -2px;
      margin-right: 0.5em;
    }
  }

  .body {

    padding: 15px 0;

    &.fixed {
      max-height: 200px;
      overflow-y: auto;
    }

  }
}

.widget.closed {
  .toggle-open .fa {
    transform: rotate(-90deg);
  }

  .body {
    max-height: 0;
    overflow: hidden;
    transition: 0.2s;
    margin: 0;
    padding: 0;
  }
}
</style>