<?php


namespace App\Repository;


use App\Model\Text;
use Illuminate\Database\Eloquent\Builder;


class TextRepository extends AbstractRepository
{
    protected array $relations = [
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
        'drawing',
        'marginWriting',
        'marginFiller',
        'writingDirections',
        'preservationStates',
        'preservationStatusW',
        'preservationStatusH',
        'translations',
        'typographyAnnotations',
        'typographyAnnotations.textSelection',
        'typographyAnnotations.override',
        'morphologyAnnotations',
        'morphologyAnnotations.textSelection',
        'morphologyAnnotations.override',
        'lexisAnnotations',
        'lexisAnnotations.textSelection',
        'lexisAnnotations.override',
        'orthographyAnnotations',
        'orthographyAnnotations.textSelection',
        'orthographyAnnotations.override',
        'morphoSyntacticalAnnotations',
        'morphoSyntacticalAnnotations.textSelection',
        'morphoSyntacticalAnnotations.override',
        'handshiftAnnotations',
        'handshiftAnnotations.textSelection',
        'handshiftAnnotations.override',
        'languageAnnotations',
        'languageAnnotations.textSelection',
        'languageAnnotations.override',
        'genericTextStructures',
        'genericTextStructures.textSelection',
        'genericTextStructures.override',
        'layoutTextStructures',
        'layoutTextStructures.textSelection',
        'layoutTextStructures.override',
        'genericTextStructureAnnotations',
        'genericTextStructureAnnotations.textSelection',
        'genericTextStructureAnnotations.override',
        'layoutTextStructureAnnotations',
        'layoutTextStructureAnnotations.textSelection',
        'layoutTextStructureAnnotations.override',
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
        'flags'
    ];
    protected string $model = Text::class;

    /**
     * @param array $project_ids
     * @return Builder
     */
    public function findByProjectIds(array $project_ids): Builder
    {
        return $this->defaultQuery()->whereHas('projects', function(Builder $query) use ($project_ids) {
            $query->whereIn('project.project_id', $project_ids);
        });
    }

    /**
     * @param array $project_names
     * @return Builder
     */
    public function findByProjectNames(array $project_names): Builder
    {
        // todo: does this work?
        return $this->defaultQuery()->whereHas('projects', function(Builder $query) use ($project_names) {
            $query->whereIn('project.name', $project_names);
        });
    }

}