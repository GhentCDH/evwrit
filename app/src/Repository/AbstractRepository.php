<?php

namespace App\Repository;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository implements RepositoryInterface
{
    protected array $relations = [];

    /** @var class-string<Model> */
    protected string $model;

    public function query(): Builder
    {
        return $this->model::query();
    }

    public function defaultQuery(): Builder
    {
        return $this->query()->with($this->relations);
    }

    public function getDefaultRelations(): array
    {
        return $this->relations;
    }
}