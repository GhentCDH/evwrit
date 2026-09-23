<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationPrescriptionLexis;

class AnnotationPrescriptionLexisSchema extends AbstractLookupSchema
{
    protected function serviceKey(): string
    {
        return 'annotation_prescription_lexis';
    }

    protected function serviceModel(): string
    {
        return AnnotationPrescriptionLexis::class;
    }

    protected function serviceName(): string
    {
        return 'Lexis prescription';
    }
}
