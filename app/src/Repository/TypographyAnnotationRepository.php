<?php


namespace App\Repository;


use App\Model\TypographyAnnotation;


class TypographyAnnotationRepository extends AbstractRepository
{
    protected array $relations = [];
    protected string $model = TypographyAnnotation::class;
}