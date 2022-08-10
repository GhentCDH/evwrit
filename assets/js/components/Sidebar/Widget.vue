<template>
    <div class="widget" :class="{closed: !isOpen}">
        <div class="sticky-block" @click="toggleOpen()">
            <div class="title">
                <span class="toggle--open-close" >
                    <i class="fa fa-angle-down" aria-hidden="true"></i>
                </span>
                <span>{{ title }}
                    <span class="count" v-if="count >= 0">{{ count }}</span>
                </span>
            </div>
        </div>
        <div class="body" :class="{fixed: isFixed}">
            <slot></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: "Widget",
    props: {
        title: {
            type: String,
            required: true
        },
        count: {
            type: Number,
            required: false
        },
        isFixed: {
            type: Boolean,
            default: false
        },
        isOpen: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        toggleOpen: function() {
            let isOpenValue = !this.isOpen;
            this.$emit('update:is-open', isOpenValue)
        },
    },
}
</script>

<style scoped lang="scss">
@import '~huisstijl2016/sass/settings';

.widget {

  .sticky-block {
  }
  .title {
    text-transform: uppercase;
    font-size: 18px;
    cursor: pointer;
    padding: 12px 0 7px;
    letter-spacing: 0.2rem;

    font-family: $default-font-family, Arial, sans-serif;
    color: #777;

    .toggle--open-close {
      float: right;
      color: #cccccc;
    }

    .count {
      border: 1px solid #eee;
      padding: 3px 5px;
      border-radius: 5px;
      font-size: 80%;
      color: #aaa;
      position: relative;
      top: 0;
      margin-right: 0.5em;
      letter-spacing: 0;
      line-height: 1;
      float: right;
    }
  }

  .body {

    padding: 15px 0;

    &.fixed {
      max-height: 200px;
      overflow-y: auto;
    }

  }

  .form-group {
    margin-bottom: 5px;

    .checkbox, .radio {
      margin-top: 0;
      margin-bottom: 0;
    }
  }
}

.widget.closed {
  .toggle--open-close .fa {
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