<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationSubtypeLexis;

class AnnotationSubtypeLexisSchema extends AbstractLookupSchema
{
    protected function serviceKey(): string
    {
        return 'annotation_subtype_lexis';
    }

    protected function serviceModel(): string
    {
        return AnnotationSubtypeLexis::class;
    }

    protected function serviceName(): string
    {
        return 'Lexis annotation subtype';
    }
}
