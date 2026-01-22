<?php


namespace App\Repository;


use App\Model\LanguageAnnotation;


class LanguageAnnotationRepository extends AbstractRepository
{
    protected array $relations = [];
    protected string $model = LanguageAnnotation::class;
}