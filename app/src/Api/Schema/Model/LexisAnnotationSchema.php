<?php

namespace App\Api\Schema\Model;

use App\Api\Schema\AbstractAnnotationSchema;
use App\Model\LexisAnnotation;

/**
 * Schema for {@see LexisAnnotation}.
 */
class LexisAnnotationSchema extends AbstractAnnotationSchema
{

    protected function schemaKey(): string
    {
        return 'lexis_annotation';
    }

    protected function schemaModel(): string
    {
        return LexisAnnotation::class;
    }
}
