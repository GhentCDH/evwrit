<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationFormulaicityLexis;

class AnnotationFormulaicityLexisSchema extends AbstractLookupSchema
{
    protected function lookupKey(): string
    {
        return 'annotation_formulaicity_lexis';
    }

    protected function lookupModel(): string
    {
        return AnnotationFormulaicityLexis::class;
    }

    protected function lookupName(): string
    {
        return 'Lexis formulaicity';
    }
}
