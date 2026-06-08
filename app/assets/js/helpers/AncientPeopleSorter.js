import {getLength} from "./ArrayHelper";

const roleMap = {
    'initiator': 10,
    'initiator?': 10,
    'receiver': 20,
    'scribe': 30,
    'legal representative': 40,
    'intermediary': 50,
    'witness': 60,
    'copyist': 60,
    'signatory': 70,
    'consenter': 80,
    'messenger': 90,
    'unknown': 200
};

const getPersonRoleWeight = (person) =>
{
    const personRole = person?.role[0]?.name ?? 'unknown'
    return roleMap[personRole] ?? 100
}

export const sortAncientPeopleByRole = (person1, person2) => {
    return getPersonRoleWeight(person1) - getPersonRoleWeight(person2);
}
