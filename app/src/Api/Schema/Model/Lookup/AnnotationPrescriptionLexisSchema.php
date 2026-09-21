<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationPrescriptionLexis;

class AnnotationPrescriptionLexisSchema extends AbstractLookupSchema
{
    protected function lookupKey(): string
    {
        return 'annotation_prescription_lexis';
    }

    protected function lookupModel(): string
    {
        return AnnotationPrescriptionLexis::class;
    }

    protected function lookupName(): string
    {
        return 'Lexis prescription';
    }
}
