import Vue from 'vue'

export default {
    data() {
        return {
            config: {
                fieldIsOpen: {},
            },
            defaultConfig: {
                fieldIsOpen: {},
            }
        }
    },
    methods: {
        collapseField(e) {
            const field = e.target.parentNode;
            const group = field.parentNode;

            // get element index
            // legend in group? correct index
            let field_index = Array.from(field.parentNode.children).indexOf(field)
            field_index -= group.querySelectorAll('legend').length ? 1 : 0;
            // get group index
            let group_index = Array.from(group.parentNode.children).indexOf(group)
            // toggle group_index:field_index in fieldIsOpen
            let index = group_index + ':' + field_index;
            Vue.set(this.config.fieldIsOpen, index, this.config.fieldIsOpen[index] !== undefined ? !this.config.fieldIsOpen[index] : true)
        },
    },
    mounted() {
        // make legends clickable
        const collapsableFields = this.$el.querySelectorAll('.vue-form-generator .form-group.collapsible > label');
        collapsableFields.forEach(label => label.onclick = this.collapseField);

        // update group visibility on config change
        this.$on('config-changed', function(config) {
            if (config) {
                this.schema.groups.forEach(function (group, group_index) {
                    group.fields.forEach(function(field, field_index) {
                        let index = group_index+':'+field_index
                        if ( field?.styleClasses )
                            field.styleClasses = field.styleClasses.replace(' collapsed','') + ((config.fieldIsOpen[index] !== undefined && config.fieldIsOpen[index]) ? '' : ' collapsed')
                    })
                })
            }
        })
    },
}
