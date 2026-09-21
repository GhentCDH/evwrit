<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationWordclassLexis;

class AnnotationWordclassLexisSchema extends AbstractLookupSchema
{
    protected function lookupKey(): string
    {
        return 'annotation_wordclass_lexis';
    }

    protected function lookupModel(): string
    {
        return AnnotationWordclassLexis::class;
    }

    protected function lookupName(): string
    {
        return 'Lexis word class';
    }
}
