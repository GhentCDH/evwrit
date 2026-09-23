<?php

namespace App\Api\Schema\Model;

use App\Api\Schema\AbstractAnnotationSchema;
use App\Model\OrthographyAnnotation;

/**
 * Schema for {@see OrthographyAnnotation}.
 */
class OrthographyAnnotationSchema extends AbstractAnnotationSchema
{

    protected function schemaKey(): string
    {
        return 'orthography_annotation';
    }

    protected function schemaModel(): string
    {
        return OrthographyAnnotation::class;
    }

}
