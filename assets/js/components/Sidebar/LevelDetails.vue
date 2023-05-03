<template>
    <div class="annotation-metadata">
        <LabelValue label="Number" :value="level.number" :inline="true"></LabelValue>

        <LabelValue label="Category" v-if="level.level_category">
            <div v-for="category in level.level_category" class="agentive-role span-list span-list--comma-separated">
                <FormatValue type="id_name" :value="category.level_category_category"></FormatValue>,
                <FormatValue type="id_name" :value="category.level_category_subcategory"></FormatValue>
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

        <h3>People</h3>

        <AncientPersonDetails v-for="person in level?.attestations ?? []" :key="person.id" :person="person" class="mbottom-small"></AncientPersonDetails>

    </div>
</template>

<script>
import LabelValue from './LabelValue'
import AncientPersonDetails from "./AncientPersonDetails.vue";
import FormatValue from "./FormatValue.vue";

export default {
    name: "LevelDetails",
    components: {
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
        }
    },
    computed: {
    },
}
</script>

<style scoped lang="scss">
</style>