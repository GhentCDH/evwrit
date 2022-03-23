<?php


namespace App\Repository;


use App\Model\GenericTextStructureAnnotation;


class GenericTextStructureAnnotationRepository extends AbstractRepository
{
    protected $relations = [];
    protected $model = GenericTextStructureAnnotation::class;
}