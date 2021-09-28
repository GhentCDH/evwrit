<?php


namespace App\Repository;


use App\Model\OrthographyAnnotation;


class OrthographyLexisAnnotationRepository extends AbastractRepository
{
    protected $relations = [];
    protected $model = OrthographyAnnotation::class;
}