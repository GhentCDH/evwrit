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