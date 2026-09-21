<?php

namespace App\Api\Schema\Field;

use App\Api\Schema\Field\Concerns\FieldConfig;
use App\Api\Schema\ResourceResolver;
use App\Model\AbstractModel;
use Symfony\Component\Routing\RouterInterface;

/**
 * A rich belongsTo relation whose own columns travel with the parent.
 *
 * Backed by a sub-schema (a list of {@see FieldInterface} for the related model,
 * which may itself contain FK or embedded fields). Two rendering modes:
 *  - nested:    the related data lives under this field's key as an object.
 *  - flattened: the sub-fields are promoted to top-level parent columns.
 *
 * Write lifecycle is "upsert child, then link": the related row is created or
 * updated from the payload, then the parent foreign key is set to point at it.
 */
class EmbeddedRelationField implements FieldInterface
{
    use FieldConfig;

    public const MODE_NESTED = 'nested';
    public const MODE_FLATTENED = 'flattened';

    private string $label;
    private int $colspan = 12;
    private bool $writable = true;
    private bool $required = false;
    private bool $exposeLink = true;
    private string $prefix = '';

    /**
     * @param class-string<AbstractModel> $relatedModel
     * @param FieldInterface[] $subFields
     */
    public function __construct(
        private readonly string $id,
        private readonly string $foreignKey,
        private readonly string $relatedModel,
        private readonly array $subFields,
        private readonly string $mode = self::MODE_NESTED,
        ?string $label = null,
    ) {
        $this->label = $label ?? ScalarField::humanize($id);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function colspan(int $colspan): static
    {
        $this->colspan = $colspan;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    public function readonly(bool $readonly = true): static
    {
        $this->writable = !$readonly;

        return $this;
    }

    /**
     * Flattened mode only: whether the intermediate foreign key (e.g. text_selection_id)
     * is emitted in output and read from input. When hidden, the related row is still
     * resolved on write via the parent's persisted link (see resolveRelated()).
     */
    public function exposeLink(bool $expose = true): static
    {
        $this->exposeLink = $expose;

        return $this;
    }

    public function hideLink(): static
    {
        return $this->exposeLink(false);
    }

    /**
     * Flattened mode only: string prepended to each promoted sub-field's exposed key,
     * e.g. prefix('textSelection:') yields "textSelection:selection_start".
     */
    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function isLinkExposed(): bool
    {
        return $this->exposeLink;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * The wire key for a sub-field in flattened mode (prefix + the sub-field's own id).
     */
    public function exposedKey(string $subId): string
    {
        return $this->prefix.$subId;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function isRequiredForCreate(): bool
    {
        return $this->required;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function isFlattened(): bool
    {
        return $this->mode === self::MODE_FLATTENED;
    }

    /**
     * @return FieldInterface[]
     */
    public function getSubFields(): array
    {
        return $this->subFields;
    }

    /**
     * Handled field-by-field by ModelService (recursively); no flat constraint list.
     */
    public function getConstraints(): array
    {
        return [];
    }

    public function toSchemaArray(RouterInterface $router, ?ResourceResolver $resources = null): array
    {
        // Object column: nested properties are full column configs (each with its own
        // fieldInput). The object property itself carries NO fieldInput — the CRUD app
        // renders object types with its own default.
        $properties = [];
        foreach ($this->subFields as $field) {
            $properties[$field->getId()] = $field->toSchemaArray($router, $resources);
        }

        $column = [
            'id' => $this->getId(),
            'label' => $this->label,
            'type' => [
                'type' => 'object',
                'properties' => $properties,
            ],
        ];
        if ($this->required) {
            $column['required'] = true;
        }

        return array_merge($column, $this->columnAttributes());
    }

    public function readValue(AbstractModel $model): mixed
    {
        /** @var AbstractModel|null $related */
        $related = $model->getAttribute($this->id);
        if ($related === null) {
            return null;
        }

        $out = ['id' => $related->getKey()];
        foreach ($this->subFields as $field) {
            $out[$field->getId()] = $field->readValue($related);
        }

        return $out;
    }

    /**
     * Upsert the related record from $subData, then link the parent foreign key.
     * Runs within the parent write transaction (see ModelService).
     *
     * @param array<string, mixed>|null $subData
     */
    public function writeValue(AbstractModel $model, mixed $subData, string $op = 'create'): void
    {
        if ($subData === null) {
            $model->setAttribute($this->foreignKey, null);

            return;
        }

        $related = $this->resolveRelated($model, $subData);

        foreach ($this->subFields as $field) {
            if (!$field->isWritableForOperation($op)) {
                continue;
            }
            if (array_key_exists($field->getId(), $subData)) {
                $field->writeValue($related, $subData[$field->getId()], $op);
            }
        }

        $related->save();
        $model->setAttribute($this->foreignKey, $related->getKey());
    }

    /**
     * Locate the related row to write: explicit sub-object id, else the parent's
     * current foreign key, else a fresh instance.
     *
     * @param array<string, mixed> $subData
     */
    private function resolveRelated(AbstractModel $model, array $subData): AbstractModel
    {
        $id = $subData['id'] ?? $model->getAttribute($this->foreignKey);

        if ($id !== null) {
            /** @var AbstractModel|null $existing */
            $existing = $this->relatedModel::query()->find($id);
            if ($existing !== null) {
                return $existing;
            }
        }

        return new $this->relatedModel();
    }
}
