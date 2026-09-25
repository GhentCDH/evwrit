<?php

namespace App\Api\Schema;

use App\Model\AbstractModel;

/**
 * A lookup schema whose key/name are derived from the model class, so every IdName lookup
 * table can be exposed without a hand-written subclass. Instances are registered per model
 * by {@see \App\DependencyInjection\RegisterLookupSchemasPass}.
 */
class GenericLookupSchema extends AbstractLookupSchema
{
    /**
     * @param class-string<AbstractModel> $model
     */
    public function __construct(private readonly string $model)
    {
    }

    protected function serviceKey(): string
    {
        // The model's own table name (snake_case of the class) — matches the keys the
        // hand-written lookup schemas use.
        return (new $this->model())->getTable();
    }

    protected function serviceModel(): string
    {
        return $this->model;
    }
}
