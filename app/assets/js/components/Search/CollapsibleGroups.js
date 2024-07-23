import Vue from 'vue'

export default {
    data() {
        return {
            config: {
                groupCollapsed: {},
            },
            defaultConfig: {
                groupCollapsed: {
                },
            }
        }
    },
    
    methods: {
        groupCollapsed(model, field) {
            return this.config.groupCollapsed[this.fieldId(field)] ?? true;
        },
        onToggleCollapsed({field, collapsed}) {
            let id = this.fieldId(field)
            this.$set(this.config.groupCollapsed, id, collapsed);
        },
        fieldId(field) {
            return field.model ?? field.id ?? null
        }
    },
}
