<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationStandardFormLexis;

class AnnotationStandardFormLexisSchema extends AbstractLookupSchema
{
    protected function lookupKey(): string
    {
        return 'annotation_standard_form_lexis';
    }

    protected function lookupModel(): string
    {
        return AnnotationStandardFormLexis::class;
    }

    protected function lookupName(): string
    {
        return 'Lexis standard form';
    }
}
