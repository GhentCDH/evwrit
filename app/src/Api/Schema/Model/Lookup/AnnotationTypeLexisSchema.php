<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationTypeLexis;

/**
 * Lookup service for {@see AnnotationTypeLexis}. Example of a one-class lookup schema;
 * others follow the same pattern.
 */
class AnnotationTypeLexisSchema extends AbstractLookupSchema
{
    protected function serviceKey(): string
    {
        return 'annotation_type_lexis';
    }

    protected function serviceModel(): string
    {
        return AnnotationTypeLexis::class;
    }

    protected function serviceName(): string
    {
        return 'Lexis annotation type';
    }
}
