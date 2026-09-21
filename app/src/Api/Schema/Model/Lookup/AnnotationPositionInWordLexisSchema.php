<?php

namespace App\Api\Schema\Model\Lookup;

use App\Api\Schema\AbstractLookupSchema;
use App\Model\Lookup\AnnotationPositionInWordLexis;

class AnnotationPositionInWordLexisSchema extends AbstractLookupSchema
{
    protected function lookupKey(): string
    {
        return 'annotation_position_in_word_lexis';
    }

    protected function lookupModel(): string
    {
        return AnnotationPositionInWordLexis::class;
    }

    protected function lookupName(): string
    {
        return 'Lexis position in word';
    }
}
