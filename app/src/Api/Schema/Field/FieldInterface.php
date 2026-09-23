<?php

namespace App\Api\Schema\Field;

use App\Api\Schema\ResourceResolver;
use App\Model\AbstractModel;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraint;

/**
 * One field/column of a model schema.
 *
 * A field knows how to describe itself (for the schema endpoint), how to read its
 * value from a model (serialization) and how to write an incoming value onto a
 * model (deserialization), plus the validation constraints for its value.
 */
interface FieldInterface
{
    /**
     * Exposed wire key of the field (config columns, request/response payloads).
     */
    public function getId(): string;

    /**
     * Internal source: DB column (scalars) or relation method (relations).
     */
    public function getSource(): string;

    public function getLabel(): string;

    /**
     * Whether the field may be written by create/update/patch. Read-only fields
     * are still serialized on output but ignored on input.
     */
    public function isWritable(): bool;

    /**
     * Effective writability for a given operation, honouring readonly() plus the
     * finer creatable/updatable gates.
     */
    public function isWritableForOperation(string $op): bool;

    /**
     * Whether the field is required when creating (and on a full PUT update).
     */
    public function isRequiredForCreate(): bool;

    /**
     * Whether a default value is configured for when the client omits this field.
     */
    public function hasDefault(): bool;

    /**
     * Resolve the configured default against the given sibling input (keyed by exposed id).
     *
     * @param array<string, mixed> $input
     */
    public function getDefault(array $input): mixed;

    /**
     * Symfony validator constraints applied to this field's (normalized) value.
     *
     * @return Constraint[]
     */
    public function getConstraints(): array;

    /**
     * Describe the field for the schema endpoint: {id, label, type, fieldInput}.
     *
     * @return array<string, mixed>
     */
    public function toSchemaArray(RouterInterface $router, ?ResourceResolver $resources = null): array;

    /**
     * Produce the output JSON value for this field from a loaded model.
     */
    public function readValue(AbstractModel $model): mixed;

    /**
     * Apply an incoming input value onto the model (writable fields only). $op lets
     * composite fields gate their sub-fields per operation (create/update/patch).
     */
    public function writeValue(AbstractModel $model, mixed $input, string $op = 'create'): void;
}
