<?php

namespace App\Api\Schema;

use App\Model\AbstractModel;

/**
 * Base schema for IdName lookup tables (id + name). A concrete lookup service is just
 * a key, a name and a model class; this declares the `name` field and all operations.
 */
abstract class AbstractLookupSchema extends AbstractSchema
{
    /**
     * The URL/registry key for this lookup, e.g. "annotation_type_lexis".
     */
    abstract protected function lookupKey(): string;

    /**
     * @return class-string<AbstractModel>
     */
    abstract protected function lookupModel(): string;

    /**
     * Human-readable label; defaults to a humanized key.
     */
    protected function lookupName(): string
    {
        return ucfirst(str_replace('_', ' ', $this->lookupKey()));
    }

    protected function configure(): void
    {
        $this->key($this->lookupKey())
            ->name($this->lookupName())
            ->model($this->lookupModel())
            ->allow(
                self::OP_FIND_ALL,
                self::OP_FIND_ONE,
                self::OP_CREATE,
                self::OP_UPDATE,
                self::OP_PATCH,
                self::OP_DELETE,
            );

        $this->primaryKey();
        $this->string('name')->required()->max(255)->exposeAs('label')->label('Label');
    }
}
