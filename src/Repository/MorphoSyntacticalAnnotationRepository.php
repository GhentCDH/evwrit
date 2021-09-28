<?php


namespace App\Repository;


use App\Model\MorphologyAnnotation;


class MorphoSyntacticalAnnotationRepository extends AbastractRepository
{
    protected $relations = [];
    protected $model = MorphologyAnnotation::class;
}