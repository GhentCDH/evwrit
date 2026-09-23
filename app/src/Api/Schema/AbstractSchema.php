<?php

namespace App\Api\Schema;

use App\Api\Schema\Field\EmbeddedRelationField;
use App\Api\Schema\Field\RelationField;
use App\Api\Schema\Field\ScalarField;
use App\Model\AbstractModel;
use LogicException;

/**
 * Base class for model schemas. Subclasses implement {@see configure()} using the
 * fluent helpers below; the definition is built lazily and cached.
 */
abstract class AbstractSchema implements SchemaInterface
{
    private bool $configured = false;

    private string $key = '';
    private string $name = '';
    /** @var class-string<AbstractModel>|null */
    private ?string $modelClass = null;
    /** @var string[] */
    private array $operations = [];
    /** @var array<string, mixed> */
    private array $metadata = [];
    private string $kind = 'custom';
    /** @var array<string, mixed> */
    private array $extra = [];
    private ?bool $listed = null;

    private ?FieldCollector $collector = null;

    /**
     * Declare key/name/model/allow/meta and fields via the fluent helpers.
     */
    abstract protected function configure(): void;

    // ---- fluent header helpers -------------------------------------------------

    protected function key(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    protected function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param class-string<AbstractModel> $modelClass
     */
    protected function model(string $modelClass): static
    {
        $this->modelClass = $modelClass;
        $this->collector = new FieldCollector($modelClass, new RelationIntrospector());

        return $this;
    }

    protected function allow(string ...$operations): static
    {
        $this->operations = $operations;

        return $this;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    protected function meta(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Resource kind (crouton config). Defaults to "custom".
     */
    protected function kind(string $kind): static
    {
        $this->kind = $kind;

        return $this;
    }

    /**
     * Custom attribute merged into the root of the schema output.
     */
    protected function attr(string $key, mixed $value): static
    {
        $this->extra[$key] = $value;

        return $this;
    }

    /**
     * Custom attributes merged into the root of the schema output (e.g. $schema).
     *
     * @param array<string, mixed> $attributes
     */
    protected function extra(array $attributes): static
    {
        $this->extra = array_merge($this->extra, $attributes);

        return $this;
    }

    /**
     * Whether this schema appears in the service directory. Overrides the class default.
     */
    protected function listed(bool $listed = true): static
    {
        $this->listed = $listed;

        return $this;
    }

    /**
     * Class-level default for directory listing; overridden by lookups.
     */
    protected function listedByDefault(): bool
    {
        return true;
    }

    // ---- fluent field helpers (delegate to the collector) ----------------------

    protected function string(string $column, ?string $label = null): ScalarField
    {
        return $this->collector()->string($column, $label);
    }

    protected function text(string $column, ?string $label = null): ScalarField
    {
        return $this->collector()->text($column, $label);
    }

    protected function integer(string $column, ?string $label = null): ScalarField
    {
        return $this->collector()->integer($column, $label);
    }

    protected function number(string $column, ?string $label = null): ScalarField
    {
        return $this->collector()->number($column, $label);
    }

    protected function boolean(string $column, ?string $label = null): ScalarField
    {
        return $this->collector()->boolean($column, $label);
    }

    protected function date(string $column, ?string $label = null): ScalarField
    {
        return $this->collector()->date($column, $label);
    }

    protected function primaryKey(string $exposeAs = 'id'): ScalarField
    {
        return $this->collector()->primaryKey($exposeAs);
    }

    /**
     * @param array<int, array{value: mixed, label: string}> $values
     */
    protected function select(string $column, array $values, ?string $label = null): ScalarField
    {
        return $this->collector()->select($column, $values, $label);
    }

    protected function relation(string $method, ?string $label = null): RelationField
    {
        return $this->collector()->relation($method, $label);
    }

    protected function relations(string ...$methods): static
    {
        $this->collector()->relations(...$methods);

        return $this;
    }

    /**
     * @param string[]|null $relations
     */
    protected function autowireResources(bool $enabled = true, ?array $relations = null): static
    {
        $this->collector()->autowireResources($enabled, $relations);

        return $this;
    }

    protected function embed(
        string $method,
        callable $subFields,
        string $mode = EmbeddedRelationField::MODE_NESTED,
    ): EmbeddedRelationField {
        return $this->collector()->embed($method, $subFields, $mode);
    }

    /**
     * Hook run on the model before every save (create/update/patch); receives ($model, $op).
     */
    protected function onSave(callable $hook): static
    {
        $this->collector()->onSave($hook);

        return $this;
    }

    /**
     * Hook run on the model before save on CREATE only; receives ($model).
     */
    protected function onCreate(callable $hook): static
    {
        $this->collector()->onCreate($hook);

        return $this;
    }

    private function collector(): FieldCollector
    {
        if ($this->collector === null) {
            throw new LogicException('Call $this->model(...) before declaring fields.');
        }

        return $this->collector;
    }

    // ---- SchemaInterface -------------------------------------------------------

    private function ensureConfigured(): void
    {
        if ($this->configured) {
            return;
        }
        $this->configure();
        $this->configured = true;
    }

    public function getKey(): string
    {
        $this->ensureConfigured();

        return $this->key;
    }

    public function getName(): string
    {
        $this->ensureConfigured();

        return $this->name;
    }

    public function getModelClass(): string
    {
        $this->ensureConfigured();

        if ($this->modelClass === null) {
            throw new LogicException(sprintf('%s did not declare a model.', static::class));
        }

        return $this->modelClass;
    }

    public function getFields(): array
    {
        $this->ensureConfigured();

        return $this->collector()->getFields();
    }

    public function getAllowedOperations(): array
    {
        $this->ensureConfigured();

        return $this->operations;
    }

    public function getMetadata(): array
    {
        $this->ensureConfigured();

        return $this->metadata;
    }

    public function getKind(): string
    {
        $this->ensureConfigured();

        return $this->kind;
    }

    public function getExtra(): array
    {
        $this->ensureConfigured();

        return $this->extra;
    }

    public function isListed(): bool
    {
        $this->ensureConfigured();

        return $this->listed ?? $this->listedByDefault();
    }

    public function getWriteHooks(): array
    {
        $this->ensureConfigured();

        return $this->collector()->getWriteHooks();
    }

    public function getEagerRelations(): array
    {
        $eager = [];
        foreach ($this->getFields() as $field) {
            if ($field instanceof RelationField) {
                $eager[] = $field->getSource();
            } elseif ($field instanceof EmbeddedRelationField) {
                $eager[] = $field->getSource();
                foreach ($field->getSubFields() as $sub) {
                    if ($sub instanceof RelationField || $sub instanceof EmbeddedRelationField) {
                        $eager[] = $field->getSource().'.'.$sub->getSource();
                    }
                }
            }
        }

        return $eager;
    }
}
