<?php


namespace App\Repository;


use App\Model\Text;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class TextRepository implements RepositoryInterface, IndexProviderInterface
{
    protected $relations = ['scripts', 'forms','languages','materials', 'socialDistances','productionStages'];

    public function find(int $id, $relations = []): Model
    {
        return $this->defaultQuery()->with($relations)->find($id);
    }

    public function findAll(int $limit = 0, $relations = [])
    {
        return $this->defaultQuery()->with($relations)->limit($limit)->get();
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
        return $this->builder()->with($this->relations);
    }
}