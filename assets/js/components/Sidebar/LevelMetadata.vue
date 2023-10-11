<template>
    <div class="level__metadata">
        <LabelValue label="Number" :value="level.number" :inline="true"></LabelValue>

        <LabelValue label="Category" v-if="level.level_category">
            <div v-for="category in level.level_category" class="span-list span-list--comma-separated">
                <FormatValue type="id_name" :value="category.level_category_category"></FormatValue>
                <template v-if="category.level_category_subcategory">
                    (<FormatValue type="id_name" :value="category.level_category_subcategory"></FormatValue>)
                </template>
            </div>
        </LabelValue>

        <LabelValue label="Production stage" :value="level.production_stage" :inline="true" type="id_name"></LabelValue>

        <LabelValue label="Agentive role">
            <div v-for="role in level.agentive_role" class="agentive-role">
                <FormatValue type="id_name" :value="role.generic_agentive_role"></FormatValue>,
                <FormatValue type="id_name" :value="role.agentive_role"></FormatValue>
            </div>
        </LabelValue>
        <LabelValue label="Communicative goal">
            <div v-for="goal in level.communicative_goal" class="communicative-goal">
                <FormatValue type="id_name" :value="goal.communicative_goal_type"></FormatValue>,
                <FormatValue type="id_name" :value="goal.communicative_goal_subtype"></FormatValue>
            </div>
        </LabelValue>

        <template v-if="hasPeople">
            <h3>People</h3>

            <PropertyGroup v-for="person in people">
                <AncientPersonDetails :person="person"
                                      :url-generator="urlGenerator">
                </AncientPersonDetails>
            </PropertyGroup>
        </template>

    </div>
</template>

<script>
import LabelValue from './LabelValue'
import AncientPersonDetails from "./AncientPersonMetadata.vue";
import FormatValue from "./FormatValue.vue";
import PropertyGroup from "./PropertyGroup.vue";

export default {
    name: "LevelDetails",
    components: {
        PropertyGroup,
        FormatValue,
        LabelValue, AncientPersonDetails
    },
    props: {
        level: {
            type: Object,
            required: true
        },
        expertMode: {
            type: Boolean,
            default: false
        },
        urlGenerator: {
            type: Function,
            default: null,
            required: true
        }
    },
    computed: {
        people() {
            return this.level?.attestations ?? []
        },
        hasPeople() {
            return this.people.length > 0
        }
    },
}
</script>

<style scoped lang="scss">
</style>