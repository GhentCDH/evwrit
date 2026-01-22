<?php


namespace App\Repository;


use App\Model\GenericTextStructureAnnotation;


class GenericTextStructureAnnotationRepository extends AbstractRepository
{
    protected array $relations = [];
    protected string $model = GenericTextStructureAnnotation::class;
}