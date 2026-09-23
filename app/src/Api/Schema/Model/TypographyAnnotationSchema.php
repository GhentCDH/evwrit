<?php

namespace App\Api\Schema\Model;

use App\Api\Schema\AbstractAnnotationSchema;
use App\Model\TypographyAnnotation;

/**
 * Schema for {@see TypographyAnnotation}.
 */
class TypographyAnnotationSchema extends AbstractAnnotationSchema
{

    protected function schemaKey(): string
    {
        return 'typography_annotation';
    }

    protected function schemaModel(): string
    {
        return TypographyAnnotation::class;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->meta([
            'color' => '#e61919',
            'isRoot' => true,
        ]);
    }

}
