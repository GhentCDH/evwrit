<?php


namespace App\Repository;


use App\Model\Text;
use Illuminate\Database\Eloquent\Builder;


class TextRepository extends AbstractRepository
{
    protected $relations = [
        'scripts',
        'forms',
        'languages',
        'materials',
        'socialDistances',
        'productionStages',
        'era',
        'projects',
        'archive',
        'keywords',
        'textType',
        'textSubtype',
        'textFormat',
        'collaborators',
        'locations',
        'locationsWritten',
        'locationsFound',
        'links',
        'images',
        'writingDirections',
//        'attestations',
        'attestations.ancientPerson',
        'attestations.ancientPerson.gender',
        'attestations.roles',
        'attestations.socialRanks',
        'attestations.age',
        'attestations.education',
        'attestations.graphType',
        'attestations.honorificEpithets',
        'attestations.occupations',
        'translations',
//        'agentiveRoles',
        'agentiveRoles.agentiveRole',
        'agentiveRoles.genericAgentiveRole',
//        'communicativeGoals',
        'communicativeGoals.communicativeGoal',
        'communicativeGoals.genericCommunicativeGoal',
//        'textSelections',
        'typographyAnnotations',
        'typographyAnnotations.textSelection',
        'morphologyAnnotations',
        'morphologyAnnotations.textSelection',
        'lexisAnnotations',
        'lexisAnnotations.textSelection',
        'orthographyAnnotations',
        'orthographyAnnotations.textSelection',
        'morphoSyntacticalAnnotations',
        'morphoSyntacticalAnnotations.textSelection',
        'handshiftAnnotations',
        'handshiftAnnotations.textSelection',
        'languageAnnotations',
        'languageAnnotations.textSelection',
        'genericTextStructure',
        'genericTextStructure.textSelection',
        'layoutTextStructure',
        'layoutTextStructure.textSelection',
        'genericTextStructureAnnotations',
        'genericTextStructureAnnotations.textSelection',
        'layoutTextStructureAnnotations.textSelection',
        'textLevels'
    ];
    protected $model = Text::class;

    /**
     * @param array $project_ids
     * @return Builder
     */
    public function findByProjectIds(array $project_ids): Builder
    {
        return $this->indexQuery()->whereHas('projects', function(Builder $query) use ($project_ids) {
            $query->whereIn('project.project_id', $project_ids);
        });
    }

    /**
     * @param array $project_ids
     * @return Builder
     */
    public function findByProjectNames(array $project_names): Builder
    {
        // todo: does this work?
        return $this->indexQuery()->whereHas('projects', function(Builder $query) use ($project_names) {
            $query->whereIn('project.name', $project_names);
        });
    }

}