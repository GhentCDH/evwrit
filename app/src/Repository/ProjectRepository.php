<?php


namespace App\Repository;


use App\Model\Project;


class ProjectRepository extends AbstractRepository
{
    protected array $relations = [
    ];
    protected string $model = Project::class;

}