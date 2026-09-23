<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationPositionInWordLexis;

class AnnotationPositionInWordLexisSchema extends AbstractLookupSchema
{
    protected function serviceKey(): string
    {
        return 'annotation_position_in_word_lexis';
    }

    protected function serviceModel(): string
    {
        return AnnotationPositionInWordLexis::class;
    }

    protected function serviceName(): string
    {
        return 'Lexis position in word';
    }
}
