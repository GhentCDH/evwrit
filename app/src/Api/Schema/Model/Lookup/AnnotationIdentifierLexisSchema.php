<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationIdentifierLexis;

class AnnotationIdentifierLexisSchema extends AbstractLookupSchema
{
    protected function serviceKey(): string
    {
        return 'annotation_identifier_lexis';
    }

    protected function serviceModel(): string
    {
        return AnnotationIdentifierLexis::class;
    }

    protected function serviceName(): string
    {
        return 'Lexis identifier';
    }
}
