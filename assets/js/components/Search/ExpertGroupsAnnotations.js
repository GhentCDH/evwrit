import Vue from 'vue'

export default {
    data() {
        return {
            defaultConfig: {
                expertMode: false,
            }
        }
    },
    methods: {
        getAnnotationFilter() {
            if ( this.model.hasOwnProperty('annotation_type') && this.model.annotation_type ) {
                return this.model.annotation_type.id;
            } else {
                return false;
            }
        },

        // update group & field visibility
        updateFieldVisibility() {
            let annotation_filter = this.getAnnotationFilter();
            let config = this.config;

            this.schema.groups.forEach(function (group, groupIndex) {
                // groups: update classes
                group.styleClasses = group.styleClasses.replace(/\s?hidden/i,'') + ((!config.expertMode && group.hasOwnProperty('expertOnly') && group.expertOnly) ? ' hidden' : '')
                // fields: update 'visible' property
                group.fields.forEach(function(field, fieldIndex) {
                    let fieldVisible = !(!config.expertMode && field.hasOwnProperty('expertOnly') && field.expertOnly);
                    if ( groupIndex === 0 ) {
                        fieldVisible = fieldVisible && (field.model === 'annotation_type' || (annotation_filter && field.model.startsWith(annotation_filter)));
                    }
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
