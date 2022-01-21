import Vue from 'vue'

export default function() {
    return {
        data() {
            return {
                defaultConfig: {
                    expertMode: false,
                }
            }
        },
        methods: {
            // update group & field visibility
            updateFieldVisibility() {
                let config = this.config;

                this.schema.groups.forEach(function (group, groupIndex) {
                    // groups: update classes
                    group.styleClasses = group.styleClasses.replace(/\s?hidden/i,'') + ((!config.expertMode && group.hasOwnProperty('expertOnly') && group.expertOnly) ? ' hidden' : '')
                    // fields: update 'visible' property
                    group.fields.forEach(function(field, fieldIndex) {
                        let fieldVisible = !(!config.expertMode && field.hasOwnProperty('expertOnly') && field.expertOnly);
                        field.visible = fieldVisible
                    })
                })
            },
        },
        mounted() {
            // update group visibility on config change
            this.$on('config-changed', function(config) {
                if (config) {
                    this.updateFieldVisibility()
                }
            })
        },
    }
}
