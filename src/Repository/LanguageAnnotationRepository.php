<?php


namespace App\Repository;


use App\Model\LanguageAnnotation;


class LanguageAnnotationRepository extends AbstractRepository
{
    protected $relations = [];
    protected $model = LanguageAnnotation::class;
}