<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationIdentifierLexis;

class AnnotationIdentifierLexisSchema extends AbstractLookupSchema
{
    protected function lookupKey(): string
    {
        return 'annotation_identifier_lexis';
    }

    protected function lookupModel(): string
    {
        return AnnotationIdentifierLexis::class;
    }

    protected function lookupName(): string
    {
        return 'Lexis identifier';
    }
}
