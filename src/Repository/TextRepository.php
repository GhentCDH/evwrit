<?php


namespace App\Repository;


use App\Model\Text;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class TextRepository implements RepositoryInterface, IndexProviderInterface
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

    public function find(int $id, $relations = []): Text
    {
        return $this->indexQuery()->with($relations)->find($id);
    }

    public function findAll(int $limit = 0, $relations = [])
    {
        return $this->indexQuery()->with($relations)->limit($limit)->get();
    }

    public function findByProjectId(int $project_id)
    {
        return $this->indexQuery()->whereHas('projects', function(Builder $query) use ($project_id) {
            $query->where('project.project_id','=', $project_id);
        });
    }

    public function builder(): Builder
    {
        return Text::query();
    }

    public function defaultQuery(): Builder
    {
        return $this->builder();
    }

    public function indexQuery(): Builder
    {
        return $this->defaultQuery()->with($this->relations);
    }
}