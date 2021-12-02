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
        'attestations',
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
        'textAgentiveRoles.agentiveRole',
        'textAgentiveRoles.genericAgentiveRole',
        'textCommunicativeGoals.communicativeGoal',
        'textCommunicativeGoals.genericCommunicativeGoal',
        'typographyAnnotations'
    ];
    protected $model = Text::class;

    /**
     * @param int $project_id
     * @return Builder
     */
    public function findByProjectId(int $project_id): Builder
    {
        return $this->indexQuery()->whereHas('projects', function(Builder $query) use ($project_id) {
            $query->where('project.project_id','=', $project_id);
        });
    }


}