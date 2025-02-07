<template>
    <div :class=orientation>
        <div>
        </div>
        <div class="margin margin-top">
            <span v-if="margin_top">{{ margin_top }} {{ unit }}</span>
            <span v-else>{{ unknown }}</span>
        </div>
        <div>
        </div>

        <div class="margin margin-left">
            <span v-if="margin_left">{{ margin_left }} {{ unit }}</span>
            <span v-else>{{ unknown }}</span>
        </div>
        <div class="page page-size">
            <span v-if="width">{{ width }} {{ unit }}</span>
            <span v-else>{{ unknown }}</span>
            <span>&nbsp;x&nbsp; </span>
            <span v-if="width">{{ height }} {{ unit }}</span>
            <span v-else>{{ unknown }}</span>
        </div>
        <div class="margin margin-right">
            <span v-if="margin_right">{{ margin_right }} {{ unit }}</span>
            <span v-else>{{ unknown }}</span>
        </div>

        <div>
        </div>
        <div class="margin margin-bottom">
            <span v-if="margin_bottom">{{ margin_bottom }} {{ unit }}</span>
            <span v-else>{{ unknown }}</span>
        </div>
        <div>
        </div>
    </div>
</template>

<script>
export default {
    name: "PageMetrics",
    props: {
        margin_top: {
            type: String,
        },
        margin_right: {
            type: String,
        },
        margin_left: {
            type: String,
        },
        margin_bottom: {
            type: String,
        },
        width: {
            type: String,
        },
        height: {
            type: String,
        },
        unit: {
            type: String,
            default: 'mm'
        },
        unknown: {
            type: String,
            default: '?'
        }
    },
    computed: {
        orientation() {
            return Number(this.height) > Number(this.width) ? "vertical" : "horizontal"
        }
    },
    data() {
        return {
        }
    },
}
</script>

<style scoped lang="scss">
.horizontal {
    display: grid;
    grid-template-rows: 40px 100px 40px;
    grid-template-columns: 32px 180px 32px;
    width: 244px;

    border: 1px solid #777;
    background: #fff;

    font-size: 12px;

    &>div {
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px dotted #777;
    }

}
.vertical {
    display: grid;
    grid-template-columns: 40px 100px 40px;
    grid-template-rows: 32px 180px 32px;
    width: 180px;

    border: 1px solid #777;
    background: #fff;

    font-size: 12px;

    &>div {
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px dotted #777;
    }

}
</style>