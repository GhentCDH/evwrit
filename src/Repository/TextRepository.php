<?php


namespace App\Repository;


use App\Model\Text;
use Illuminate\Database\Eloquent\Builder;


class TextRepository extends AbstractRepository
{
    protected $relations = [
        'era',
        'scripts',
        'forms',
        'languages',
        'materials',
        'socialDistances',
        'projects',
        'keywords',
        'archive',
        'textFormat',
        'collaborators',
        'locations',
        'locationsWritten',
        'locationsFound',
        'links',
        'images',
        'writingDirections',
        'preservationStates',
        'preservationStatusW',
        'preservationStatusH',
        'translations',
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
        'handshiftAnnotations.attestation.ancientPerson',
        'languageAnnotations',
        'languageAnnotations.textSelection',
        'genericTextStructures',
        'genericTextStructures.textSelection',
        'layoutTextStructures',
        'layoutTextStructures.textSelection',
        'genericTextStructureAnnotations',
        'genericTextStructureAnnotations.textSelection',
        'layoutTextStructureAnnotations',
        'layoutTextStructureAnnotations.textSelection',
        'textLevels',
        'textLevels.attestations',
        'textLevels.attestations.ancientPerson',
        'textLevels.attestations.ancientPerson.gender',
        'textLevels.attestations.roles',
        'textLevels.attestations.socialRanks',
        'textLevels.attestations.age',
        'textLevels.attestations.education',
        'textLevels.attestations.graphType',
        'textLevels.attestations.honorificEpithets',
        'textLevels.attestations.occupations',
        'textLevels.productionStages',
        'textLevels.levelCategories',
        'textLevels.levelCategories.levelCategory',
        'textLevels.levelCategories.levelSubcategory',
        'textLevels.levelCategories.levelHypercategory',
        'textLevels.agentiveRoles',
        'textLevels.agentiveRoles.genericAgentiveRole',
        'textLevels.communicativeGoals',
        'textLevels.communicativeGoals.communicativeGoalType',
        'textLevels.communicativeGoals.communicativeGoalSubtype',
        'textLevels.greekLatins',
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