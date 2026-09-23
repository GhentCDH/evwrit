<?php

namespace App\Api\Schema\Model;

use App\Api\Schema\AbstractAnnotationSchema;
use App\Api\Schema\AbstractSchema;
use App\Api\Schema\Field\EmbeddedRelationField;
use App\Api\Schema\FieldCollector;
use App\Model\HandshiftAnnotation;

/**
 * Schema for {@see HandshiftAnnotation}. Reference implementation: adding another
 * model is a class like this one.
 */
class HandshiftAnnotationSchema extends AbstractAnnotationSchema
{

    protected function schemaKey(): string
    {
        return 'handshift_annotation';
    }

    protected function schemaModel(): string
    {
        return HandshiftAnnotation::class;
    }

    protected function configure(): void
    {
        parent::configure();

        // scalar columns
        $this->integer('internal_hand_num', 'Internal hand number')->colspan(12);
        $this->string('status')->colspan(12)->max(255);
        $this->text('comment')->colspan(12);

        $this->meta([
            'color' => '#6200D1',
            'isRoot' => true,
        ]);


        // plain FK (no name column -> label falls back to id)
//        $this->relation('attestation');
    }
}
