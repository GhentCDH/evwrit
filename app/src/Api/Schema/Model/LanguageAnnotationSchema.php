<?php

namespace App\Api\Schema\Model;

use App\Api\Schema\AbstractAnnotationSchema;
use App\Model\LanguageAnnotation;

/**
 * Schema for {@see LanguageAnnotation}.
 */
class LanguageAnnotationSchema extends AbstractAnnotationSchema
{

    protected function schemaKey(): string
    {
        return 'language_annotation';
    }

    protected function schemaModel(): string
    {
        return LanguageAnnotation::class;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->meta([
            'color' => '#1E64C8',
            'isRoot' => true,
        ]);
    }
}
