<?php

namespace App\Api\Schema;

use App\Api\Schema\Field\EmbeddedRelationField;
use App\Model\AbstractModel;
use App\Model\TextSelection;

/**
 * Base schema for IdName lookup tables (id + name). A concrete lookup service is just
 * a key, a name and a model class; this declares the `name` field and all operations.
 */
abstract class AbstractAnnotationSchema extends AbstractSchema
{
    /**
     * The URL/registry key for this lookup, e.g. "annotation_type_lexis".
     */
    abstract protected function schemaKey(): string;

    /**
     * @return class-string<AbstractModel>
     */
    abstract protected function schemaModel(): string;

    /**
     * Human-readable label; defaults to a humanized key.
     */
    protected function schemaName(): string
    {
        return ucfirst(str_replace('_', ' ', $this->schemaKey()));
    }

    /**
     * Lookup relations to expose as autocomplete columns. Defaults to auto-discovering
     * every belongsTo relation on the model that points at an id/name lookup; override to
     * pin or restrict the list explicitly.
     *
     * @return array<string>
     */
    protected function modelRelations(): array
    {
        return (new RelationIntrospector())->discoverLookupRelations($this->schemaModel());
    }

    protected function configure(): void
    {
        $this->key($this->schemaKey())
            ->name($this->schemaName())
            ->model($this->schemaModel())
            ->allow(
                self::OP_FIND_ALL,
                self::OP_FIND_ONE,
                self::OP_CREATE,
                self::OP_UPDATE,
                self::OP_PATCH,
                self::OP_DELETE,
            );

        // auto-fill each lookup's autocomplete resource from the registry (where a
        // schema exists for the related model); per-relation ->lookup()/->noResource() win.
        $this->autowireResources(true);

        // lookup foreign keys as {id,label} autocomplete, fully reflection-derived
        $this->primaryKey('id')->hiddenInForm()->noFieldInput();

        // add relations if any are defined in the concrete schema
        $relations = $this->modelRelations();
        if (!empty($relations)) {
            $this->relations(...$relations);
        }

        $this->date('created')->hiddenInForm()->noFieldInput();
        $this->date('updated')->hiddenInForm()->noFieldInput();

        // the annotated text selection, flattened onto the annotation (link hidden)
        $this->embed('textSelection', function (FieldCollector $f): void {
            $f->integer('text_id')->hidden()->required()->hiddenInForm()->noFieldInput()->exposeAs('source_id'); // set programmatically, hidden in UI
            $f->integer('selection_start')->required()->hiddenInForm()->noFieldInput()->exposeAs('start');
            $f->integer('selection_end')->required()->hiddenInForm()->noFieldInput()->exposeAs('end');
            $f->text('text')->required()->hiddenInForm()->noFieldInput()->exposeAs('exact');
            // NOT NULL columns not part of the public contract, populated by the hooks below
            // so a selection can be created from source_id/start/end/exact.
            // text_edited: default once at creation, never clobber later edits.
            $f->onCreate(static fn (TextSelection $s) => $s->text_edited ??= $s->text);
            // selection_length: derived — keep in sync on every write (create/update/patch).
            $f->onSave(static function (TextSelection $s): void {
                $s->selection_length = (int) $s->selection_end - (int) $s->selection_start;
            });
        }, EmbeddedRelationField::MODE_NESTED)->hideLink()->exposeAs('selector');

    }
}
