<?php


namespace App\Repository;


use App\Model\MorphologyAnnotation;


class MorphoSyntacticalAnnotationRepository extends AbstractRepository
{
    protected $relations = [];
    protected $model = MorphologyAnnotation::class;
}