<template>
    <div class="ancient-person">
        <div class="ancient-person--name">
            {{ person.name }}
        </div>
        <div class="ancient-person--metadata">
            <LabelValue label="Trismegistos ID" :value="person.tm_id" :url="getTmPersonUrl"></LabelValue>
            <LabelValue label="Role" :value="person.role"  type="id_name" :ignore-value="['Unknown','unknown']" :value-class="getPersonRoleClass"></LabelValue>
            <LabelValue label="Age" :value="person.age"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
            <LabelValue label="Gender" :value="person.gender"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
            <LabelValue label="Education" :value="person.education"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
            <LabelValue label="Occupation" :value="person.occupation"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
            <LabelValue label="Social Rank" :value="person.social_rank"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
            <LabelValue label="Graph Type" :value="person.graph_type"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
            <LabelValue label="Honorific Epithet" :value="person.honorific_epithet"  type="id_name" :ignore-value="['Unknown','unknown']"></LabelValue>
        </div>
    </div>
</template>

<script>
import LabelValue from './LabelValue'

export default {
    name: "AncientPersonDetails",
    components: {
        LabelValue
    },
    props: {
        person: {
            type: Object,
            required: true
        },
        expertMode: {
            type: Boolean,
            default: false
        }
    },
    computed: {},
    methods: {
        getPersonRoleClass(role) {
            let ret = 'label label-default';
            switch(role?.name) {
                case 'initiator': ret = 'label label-success'; break;
                case 'receiver': ret = 'label label-info'; break;
                case 'scribe': ret = 'label label-primary'; break;
            }
            return ret
        },
        getTmPersonUrl(id) {
            return 'https://www.trismegistos.org/person/' + id
        }
    }
}
</script>

<style scoped lang="scss">
</style>