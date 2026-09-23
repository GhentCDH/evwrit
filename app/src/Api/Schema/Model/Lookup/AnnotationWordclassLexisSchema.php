<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationWordclassLexis;

class AnnotationWordclassLexisSchema extends AbstractLookupSchema
{
    protected function serviceKey(): string
    {
        return 'annotation_wordclass_lexis';
    }

    protected function serviceModel(): string
    {
        return AnnotationWordclassLexis::class;
    }

    protected function serviceName(): string
    {
        return 'Lexis word class';
    }
}
