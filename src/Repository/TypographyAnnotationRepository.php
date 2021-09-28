<?php


namespace App\Repository;


use App\Model\TypographyAnnotation;


class TypographyAnnotationRepository extends AbastractRepository
{
    protected $relations = [];
    protected $model = TypographyAnnotation::class;
}