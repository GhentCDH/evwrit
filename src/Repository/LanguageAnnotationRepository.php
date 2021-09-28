<?php


namespace App\Repository;


use App\Model\LanguageAnnotation;


class LanguageAnnotationRepository extends AbastractRepository
{
    protected $relations = [];
    protected $model = LanguageAnnotation::class;
}