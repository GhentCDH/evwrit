<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationProscriptionLexis;

class AnnotationProscriptionLexisSchema extends AbstractLookupSchema
{
    protected function serviceKey(): string
    {
        return 'annotation_proscription_lexis';
    }

    protected function serviceModel(): string
    {
        return AnnotationProscriptionLexis::class;
    }

    protected function serviceName(): string
    {
        return 'Lexis proscription';
    }
}
