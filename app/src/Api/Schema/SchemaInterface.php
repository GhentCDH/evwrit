<?php

namespace App\Api\Schema;

use App\Api\Schema\Field\FieldInterface;
use App\Model\AbstractModel;

/**
 * Describes what an external application may do with one model, and how its data
 * maps to/from the wire. Implementations are auto-tagged (app.api.schema) and
 * collected by {@see SchemaRegistry}.
 */
interface SchemaInterface
{
    public const OP_FIND_ALL = 'findAll';
    public const OP_FIND_ONE = 'findOne';
    public const OP_CREATE = 'create';
    public const OP_UPDATE = 'update';
    public const OP_PATCH = 'patch';
    public const OP_DELETE = 'delete';

    /**
     * Unique model key used in the URL, e.g. "handshift".
     */
    public function getKey(): string;

    /**
     * Human-readable label, e.g. "Handshift annotation".
     */
    public function getName(): string;

    /**
     * @return class-string<AbstractModel>
     */
    public function getModelClass(): string;

    /**
     * @return FieldInterface[]
     */
    public function getFields(): array;

    /**
     * @return string[] subset of the OP_* constants
     */
    public function getAllowedOperations(): array;

    /**
     * Free-form metadata block emitted under "annotation" in the schema output.
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array;

    /**
     * Relation names to eager-load so read serialization has FK labels / embedded data.
     *
     * @return string[]
     */
    public function getEagerRelations(): array;

    /**
     * Resource kind (crouton config). Defaults to "custom".
     */
    public function getKind(): string;

    /**
     * Custom attributes merged into the root of the schema output (e.g. $schema).
     *
     * @return array<string, mixed>
     */
    public function getExtra(): array;
}
