<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationProscriptionLexis;

class AnnotationProscriptionLexisSchema extends AbstractLookupSchema
{
    protected function lookupKey(): string
    {
        return 'annotation_proscription_lexis';
    }

    protected function lookupModel(): string
    {
        return AnnotationProscriptionLexis::class;
    }

    protected function lookupName(): string
    {
        return 'Lexis proscription';
    }
}
