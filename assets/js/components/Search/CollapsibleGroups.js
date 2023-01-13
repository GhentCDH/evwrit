import Vue from 'vue'

export default {
    data() {
        return {
            config: {
                groupIsOpen: {},
            },
            defaultConfig: {
                groupIsOpen: {
                },
            }
        }
    },
    
    methods: {
        groupCollapsed(model, field) {
            return this.config.groupIsOpen[this.fieldId(field)] ?? true;
        },
        onToggleCollapsed({field, collapsed}) {
            let id = this.fieldId(field)
            this.$set(this.config.groupIsOpen, id, collapsed);
        },
        fieldId(field) {
            return field.model ?? field.id ?? null
        }
    },
}
