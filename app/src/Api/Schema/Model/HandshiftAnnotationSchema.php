<?php

namespace App\Api\Schema\Model;

use App\Api\Schema\AbstractSchema;
use App\Api\Schema\Field\EmbeddedRelationField;
use App\Api\Schema\FieldCollector;
use App\Model\HandshiftAnnotation;

/**
 * Schema for {@see HandshiftAnnotation}. Reference implementation: adding another
 * model is a class like this one.
 */
class HandshiftAnnotationSchema extends AbstractSchema
{
    protected function configure(): void
    {
        $this->key('handshift')
            ->name('Handshift annotation')
            ->model(HandshiftAnnotation::class)
            ->allow(
                self::OP_FIND_ALL,
                self::OP_FIND_ONE,
                self::OP_CREATE,
                self::OP_UPDATE,
                self::OP_PATCH,
                self::OP_DELETE,
            )
            ->meta([
                'color' => '#7a8800',
                'icon' => 'hand',
                'isRoot' => false,
            ]);

        // scalar columns
        $this->integer('internal_hand_num', 'Internal hand number')->colspan(6);
        $this->string('status')->colspan(6)->max(255);
        $this->text('comment')->colspan(12);

        // rich related record: the annotated text selection (upserted on write)
        $this->embed('textSelection', function (FieldCollector $f): void {
            $f->integer('selection_start')->required();
            $f->integer('selection_end')->required();
            $f->integer('selection_length')->nullable();
            $f->integer('line_number_start')->nullable();
            $f->integer('line_number_end')->nullable();
            $f->text('text')->nullable();
            $f->text('text_edited')->nullable();
            $f->relation('sourceText')->labelBy('title'); // Text has no "name"
        }, EmbeddedRelationField::MODE_NESTED)->required();

        // plain FK (no name column -> label falls back to id)
        $this->relation('attestation');

        // lookup foreign keys ({id,label} autocomplete), fully reflection-derived
        $this->relations(
            'abbreviation', 'accentuation', 'connectivity', 'correction', 'curvature',
            'degreeOfFormality', 'expansion', 'lineation', 'orientation', 'punctuation',
            'regularity', 'scriptType', 'slope', 'wordSplitting',
        );
    }
}
