<?php

namespace App\Api\Schema\Model;

use App\Api\Schema\AbstractSchema;
use App\Api\Schema\Field\EmbeddedRelationField;
use App\Api\Schema\FieldCollector;
use App\Model\LexisAnnotation;

/**
 * Schema for {@see LexisAnnotation}.
 *
 * Demonstrates a flattened embedded relation: the annotated TextSelection is folded
 * into the annotation as top-level fields. The internal link (text_selection_id) is
 * hidden; on write the existing TextSelection is resolved via the parent's persisted
 * link (or created for a new annotation). text_id is a real TextSelection column,
 * exposed but rendered hidden (set programmatically, never picked by the user).
 */
class LexisAnnotationSchema extends AbstractSchema
{
    protected function configure(): void
    {
        $this->key('lexis')
            ->name('Lexis annotation')
            ->model(LexisAnnotation::class)
            ->allow(
                self::OP_FIND_ALL,
                self::OP_FIND_ONE,
                self::OP_CREATE,
                self::OP_UPDATE,
                self::OP_PATCH,
                self::OP_DELETE,
            );
//            ->meta([
//                'color' => '#1f6f8b',
//                'icon' => 'book',
//                'isRoot' => false,
//            ]);

        // auto-fill each lookup's autocomplete resource from the registry (where a
        // schema exists for the related model); per-relation ->lookup()/->noResource() win.
        $this->autowireResources(true);

        // lookup foreign keys as {id,label} autocomplete, fully reflection-derived
        $this->primaryKey('id')->hiddenInForm()->noFieldInput();

        $this->relations(
            'standardForm', 'type', 'subtype', 'wordclass', 'formulaicity',
            'prescription', 'proscription', 'positionInWord', 'identifier',
        );

        $this->date('created')->hiddenInForm()->noFieldInput();
        $this->date('updated')->hiddenInForm()->noFieldInput();

        // the annotated text selection, flattened onto the annotation (link hidden)
        $this->embed('textSelection', function (FieldCollector $f): void {
            $f->integer('text_id')->hidden()->required()->hiddenInForm()->noFieldInput()->exposeAs('source_id'); // set programmatically, hidden in UI
            $f->integer('selection_start')->required()->hiddenInForm()->noFieldInput()->exposeAs('start');
            $f->integer('selection_end')->required()->hiddenInForm()->noFieldInput()->exposeAs('end');
//            $f->integer('selection_length')->required()->hiddenInForm()->noFieldInput()->exposeAs('length');
            $f->text('text')->required()->hiddenInForm()->noFieldInput()->exposeAs('exact');
//            $f->text('text_edited')->required()->hiddenInForm()->noFieldInput();
        }, EmbeddedRelationField::MODE_NESTED)->hideLink()->exposeAs('selector');

    }
}
