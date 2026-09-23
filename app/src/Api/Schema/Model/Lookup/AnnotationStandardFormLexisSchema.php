<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationStandardFormLexis;

class AnnotationStandardFormLexisSchema extends AbstractLookupSchema
{
    protected function serviceKey(): string
    {
        return 'annotation_standard_form_lexis';
    }

    protected function serviceModel(): string
    {
        return AnnotationStandardFormLexis::class;
    }

    protected function serviceName(): string
    {
        return 'Lexis standard form';
    }
}
