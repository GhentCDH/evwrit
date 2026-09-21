<?php

namespace App\Api\Schema\Field;

use App\Api\Schema\Field\Concerns\FieldConfig;
use App\Api\Schema\FieldCollector;
use App\Api\Schema\RelationIntrospector;
use App\Api\Schema\ResourceResolver;
use App\Api\Validator\FkExists;
use App\Model\AbstractModel;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A belongsTo relation exposed as an {id, label} object with an autocomplete widget.
 *
 * On output the related record is serialized to {id, label}; on input only the id is
 * read and stored into the foreign-key column (the label is ignored). The related
 * record is never modified (reference-only) — use {@see EmbeddedRelationField} for
 * rich records that must be upserted.
 */
class RelationField implements FieldInterface
{
    use FieldConfig;

    private string $label;
    private string $widget = 'autocomplete';
    private int $colspan = 12;
    private bool $writable = true;
    private bool $required = false;
    private ?string $lookup = null;
    private string $labelAttribute;
    /** null = inherit the collector default at finalization; true/false = explicit. */
    private ?bool $autowire = null;
    /** @var FieldInterface[] extra read-only properties included in the object (projection) */
    private array $projection = [];

    /**
     * @param class-string<AbstractModel> $relatedModel
     */
    public function __construct(
        private readonly string $id,
        private readonly string $foreignKey,
        private readonly string $relatedModel,
        string $labelAttribute = 'name',
        ?string $label = null,
    ) {
        $this->label = $label ?? ScalarField::humanize($id);
        $this->labelAttribute = $labelAttribute;
    }

    /**
     * Which attribute of the related model provides the display label (e.g. "title").
     */
    public function labelBy(string $attribute): static
    {
        $this->labelAttribute = $attribute;

        return $this;
    }

    /**
     * Include extra read-only properties of the related model in the object, on top of
     * {id, label}. The relation stays reference-only: these are serialized on read but
     * ignored on write (only the id is stored). The callback receives a FieldCollector
     * for the related model.
     *
     * Example: ->object(fn (FieldCollector $f) => $f->text('text')->integer('selection_start'))
     */
    public function object(callable $fields): static
    {
        $collector = new FieldCollector($this->relatedModel, new RelationIntrospector());
        $fields($collector);
        $this->projection = $collector->getFields();

        return $this;
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
     * URL (or template) the frontend uses to search options for the autocomplete.
     * An explicit lookup always wins over autowiring.
     */
    public function lookup(string $url): static
    {
        $this->lookup = $url;

        return $this;
    }

    /**
     * Enable/disable auto-filling the resource URL from the schema registry for this
     * relation, overriding the collector default.
     */
    public function autowireResource(bool $enabled = true): static
    {
        $this->autowire = $enabled;

        return $this;
    }

    /**
     * Never emit an autowired resource for this relation (overrides the collector default).
     */
    public function noResource(): static
    {
        return $this->autowireResource(false);
    }

    /**
     * Explicit per-field autowire setting, or null when it should inherit the collector default.
     */
    public function getAutowire(): ?bool
    {
        return $this->autowire;
    }

    public function setAutowire(bool $enabled): void
    {
        $this->autowire = $enabled;
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

    /**
     * Constraints applied to the *normalized* id value (see {@see extractId()}).
     */
    public function getConstraints(): array
    {
        $value = [
            new Assert\Type('numeric'),
            new FkExists($this->relatedModel),
        ];

        if ($this->required) {
            return $value;
        }

        return [new Assert\AtLeastOneOf([
            new Assert\IsNull(),
            new Assert\Sequentially($value),
        ])];
    }

    public function toSchemaArray(RouterInterface $router, ?ResourceResolver $resources = null): array
    {
        $options = [
            'colspan' => $this->colspan,
            'valueKey' => 'id',
            'labelKey' => 'label',
        ];

        // Precedence: explicit lookup() > autowired registry resource > none.
        $resource = $this->lookup;
        if ($resource === null && $this->autowire === true && $resources !== null) {
            $resource = $resources->resolve($this->relatedModel);
        }
        if ($resource !== null) {
            $options['resource'] = $resource;
        }

        $properties = [
            'id' => ['type' => 'integer'],
            'label' => ['type' => 'string'],
        ];
        foreach ($this->projection as $sub) {
            $properties[$sub->getId()] = $sub->toSchemaArray($router, $resources);
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
        $column = array_merge($column, $this->columnAttributes());
        if ($this->wantsFieldInput()) {
            $column['fieldInput'] = $this->buildFieldInput($this->widget, $options);
        }

        return $column;
    }

    public function readValue(AbstractModel $model): mixed
    {
        /** @var AbstractModel|null $related */
        $related = $model->getAttribute($this->id);
        if ($related === null) {
            return null;
        }

        $out = [
            'id' => $related->getKey(),
            'label' => $this->resolveLabel($related),
        ];
        foreach ($this->projection as $sub) {
            $out[$sub->getId()] = $sub->readValue($related);
        }

        return $out;
    }

    public function writeValue(AbstractModel $model, mixed $input, string $op = 'create'): void
    {
        $model->setAttribute($this->foreignKey, $this->extractId($input));
    }

    /**
     * Normalize an incoming FK value ({id,label} object, bare id, or null) to its id.
     */
    public function extractId(mixed $input): mixed
    {
        if ($input === null) {
            return null;
        }
        if (is_array($input)) {
            return $input['id'] ?? null;
        }

        return $input;
    }

    private function resolveLabel(AbstractModel $related): string
    {
        if ($this->labelAttribute !== '' && $related->getAttribute($this->labelAttribute) !== null) {
            return (string) $related->getAttribute($this->labelAttribute);
        }

        if (method_exists($related, '__toString')) {
            return (string) $related;
        }

        return (string) $related->getKey();
    }
}
