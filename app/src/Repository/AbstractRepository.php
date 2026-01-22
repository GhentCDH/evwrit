<?php

namespace App\Repository;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository implements RepositoryInterface
{
    protected array $relations = [];

    public function builder(): Builder
    {
        return $this->model::query();
    }

    public function defaultQuery(): Builder
    {
        return $this->builder();
    }

    public function indexQuery(): Builder
    {
        return $this->defaultQuery()->with($this->relations);
    }

    public function find(int $id, $relations = []): ?Model
    {
        return $this->indexQuery()->with($relations)->find($id);
    }

    public function findAll(int $limit = 0, $relations = []): Collection
    {
        return $this->indexQuery()->with($relations)->limit($limit)->get();
    }
}