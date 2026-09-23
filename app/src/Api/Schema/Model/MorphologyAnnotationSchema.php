<?php

namespace App\Api\Schema\Model;

use App\Api\Schema\AbstractAnnotationSchema;
use App\Model\MorphologyAnnotation;

/**
 * Schema for {@see MorphologyAnnotation}.
 */
class MorphologyAnnotationSchema extends AbstractAnnotationSchema
{

    protected function schemaKey(): string
    {
        return 'morphology_annotation';
    }

    protected function schemaModel(): string
    {
        return MorphologyAnnotation::class;
    }

}
