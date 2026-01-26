<template>
    <div class="level__metadata">
        <LabelValue label="Number" :value="level.number" :inline="true"></LabelValue>

        <LabelValue label="Category" v-if="level.level_category">
            <div v-for="(category, index) in level.level_category" :key="category.id || index" class="span-list span-list--comma-separated">
                <FormatValue type="id_name" :value="category.level_category_category"></FormatValue>
                <template v-if="category.level_category_subcategory">
                    (<FormatValue type="id_name" :value="category.level_category_subcategory"></FormatValue>)
                </template>
            </div>
        </LabelValue>

        <LabelValue label="Production stage" :value="level.production_stage" :inline="true" type="id_name"></LabelValue>

        <LabelValue label="Agentive role" v-if="level?.agentive_role?.length">
            <div v-for="(role, index) in level?.agentive_role" :key="role.id || index" class="agentive-role">
                <FormatValue type="id_name" :value="role.generic_agentive_role"></FormatValue>,
                <FormatValue type="id_name" :value="role.agentive_role"></FormatValue>
            </div>
        </LabelValue>
        <LabelValue label="Communicative goal" v-if="level?.communicative_goal?.length">
            <div v-for="(goal, index) in level?.communicative_goal" :key="goal.id || index" class="communicative-goal">
                <FormatValue type="id_name" :value="goal.communicative_goal_type"></FormatValue>,
                <FormatValue type="id_name" :value="goal.communicative_goal_subtype"></FormatValue>
            </div>
        </LabelValue>

        <LabelValue label="Ancient category" v-if="level?.greek_latin?.length">
            <div v-for="(greek_latin, index) in level?.greek_latin" :key="greek_latin.id || index" class="span-list span-list--comma-separated">
                <FormatValue type="string" :value="greek_latin.label"></FormatValue>
                (<FormatValue type="string" :value="greek_latin.english"></FormatValue>)
            </div>
        </LabelValue>

        <template v-if="hasPeople">
            <h3>People</h3>

            <PropertyGroup v-for="(person, index) in people" :key="person.id || person.tm_id || index">
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