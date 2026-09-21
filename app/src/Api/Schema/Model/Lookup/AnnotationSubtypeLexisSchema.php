<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationSubtypeLexis;

class AnnotationSubtypeLexisSchema extends AbstractLookupSchema
{
    protected function lookupKey(): string
    {
        return 'annotation_subtype_lexis';
    }

    protected function lookupModel(): string
    {
        return AnnotationSubtypeLexis::class;
    }

    protected function lookupName(): string
    {
        return 'Lexis annotation subtype';
    }
}
