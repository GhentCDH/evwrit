<?php


namespace App\Repository;


use App\Model\MorphologyAnnotation;


class MorphoSyntacticalAnnotationRepository extends AbstractRepository
{
    protected array $relations = [];
    protected string $model = MorphologyAnnotation::class;
}