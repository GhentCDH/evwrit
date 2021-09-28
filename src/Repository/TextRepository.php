<?php


namespace App\Repository;


use App\Model\Text;
use Illuminate\Database\Eloquent\Builder;


class TextRepository extends AbastractRepository
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