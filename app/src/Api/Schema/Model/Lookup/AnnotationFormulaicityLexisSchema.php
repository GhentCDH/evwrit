<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationFormulaicityLexis;

class AnnotationFormulaicityLexisSchema extends AbstractLookupSchema
{
    protected function serviceKey(): string
    {
        return 'annotation_formulaicity_lexis';
    }

    protected function serviceModel(): string
    {
        return AnnotationFormulaicityLexis::class;
    }

    protected function serviceName(): string
    {
        return 'Lexis formulaicity';
    }
}
