<?php


namespace App\Repository;


use App\Model\TypographyAnnotation;


class TypographyAnnotationRepository extends AbstractRepository
{
    protected $relations = [];
    protected $model = TypographyAnnotation::class;
}