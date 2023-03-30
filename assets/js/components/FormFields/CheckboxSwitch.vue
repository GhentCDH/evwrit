<template>
    <div class="checkbox checkbox-switch">
        <label>
            <input type="checkbox" :name="name" v-bind:checked="value" v-on:change="$emit('input', $event.target.checked)" :disabled="disabled"/>
            <span></span>
            {{ label }}
        </label>
        <slot></slot>
    </div>
</template>

<script>
export default {
    name: "CheckboxSwitch",
    props: {
        name: {
            type: String,
            required: false,
            default: null
        },
        value: {
            required: true,
            type: Boolean,
            default: false
        },
        label: {
            type: String,
            default: ''
        },
        disabled: {
            type: Boolean,
            default: false,
        }
    }
}
</script>