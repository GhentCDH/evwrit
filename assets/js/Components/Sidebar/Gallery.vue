<template>
    <div class="gallery">
        <div class="thumb" v-for="(image,index) in images" :key="index">
            <img :src="image.src" style="width: 100%; cursor: pointer" @click="onClick(index,url)">
        </div>
    </div>
</template>

<script>
import VueCookies from 'vue-cookies'

import Vue from 'vue'
Vue.use(VueCookies)

export default {
    name: "Gallery",
    props: {
        images: {
            type: Array,
            required: true
        },
        onClick: {
            required: true
        }
    },
}
</script>

<style scoped lang="scss">
.gallery {
  display: flex;
  flex-wrap: wrap;
  flex-direction: row;
  align-items: flex-start;
  margin: 0 -2px;

  .thumb {
    border: 4px solid white;
    margin: 2px;
    flex-basis: calc(50% - 8px);
  }
}
</style>