<?php

namespace App\Api\Schema\Field\Concerns;

use App\Api\Schema\SchemaInterface;

/**
 * Shared, optional per-field configuration: visibility flags, create/update gating,
 * layout position, custom attributes and extra widget options. All values are
 * tri-state / opt-in and only emitted into the schema output when explicitly set.
 *
 * The consuming class must implement FieldInterface (in particular isWritable()).
 */
trait FieldConfig
{
    /** null = exposed name equals the internal source ($this->id). */
    private ?string $exposedId = null;
    private ?bool $hiddenInTable = null;
    private ?bool $hiddenInForm = null;
    private ?bool $hiddenInView = null;
    private ?bool $creatable = null;
    private ?bool $updatable = null;
    private ?int $position = null;
    private bool $noFieldInput = false;
    /** @var array<string, mixed> custom attributes merged into the column node */
    private array $extra = [];
    /** @var array<string, mixed> extra options merged into fieldInput.options */
    private array $fieldInputOptions = [];

    /**
     * Expose this field under a different name than its internal source (DB column
     * for scalars, relation method for relations). Applies to every operation.
     */
    public function exposeAs(string $name): static
    {
        $this->exposedId = $name;

        return $this;
    }

    /**
     * The exposed wire key (config columns, request/response payloads). Defaults to
     * the internal source ($this->id) unless exposeAs() overrides it.
     */
    public function getId(): string
    {
        return $this->exposedId ?? $this->id;
    }

    /**
     * The internal source: the DB column (scalars) or relation method (relations),
     * used for reflection, eager loading and get/setAttribute.
     */
    public function getSource(): string
    {
        return $this->id;
    }

    public function hiddenInTable(bool $hidden = true): static
    {
        $this->hiddenInTable = $hidden;

        return $this;
    }

    public function hiddenInForm(bool $hidden = true): static
    {
        $this->hiddenInForm = $hidden;

        return $this;
    }

    public function hiddenInView(bool $hidden = true): static
    {
        $this->hiddenInView = $hidden;

        return $this;
    }

    /**
     * Hide the field everywhere (table, form and view).
     */
    public function hidden(): static
    {
        return $this->hiddenInTable()->hiddenInForm()->hiddenInView();
    }

    public function creatable(bool $creatable = true): static
    {
        $this->creatable = $creatable;

        return $this;
    }

    public function updatable(bool $updatable = true): static
    {
        $this->updatable = $updatable;

        return $this;
    }

    /**
     * Layout position hint, emitted as fieldInput.position.
     */
    public function position(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Omit the field's fieldInput entirely: the value is set by another process, not
     * a form (e.g. hidden, programmatically-populated columns).
     */
    public function noFieldInput(): static
    {
        $this->noFieldInput = true;

        return $this;
    }

    protected function wantsFieldInput(): bool
    {
        return !$this->noFieldInput;
    }

    /**
     * Custom attribute merged into the column node.
     */
    public function attr(string $key, mixed $value): static
    {
        $this->extra[$key] = $value;

        return $this;
    }

    /**
     * Custom attributes merged into the column node.
     *
     * @param array<string, mixed> $attributes
     */
    public function extra(array $attributes): static
    {
        $this->extra = array_merge($this->extra, $attributes);

        return $this;
    }

    /**
     * Extra options merged into fieldInput.options (e.g. select "values", "clearable").
     *
     * @param array<string, mixed> $options
     */
    public function fieldInputOptions(array $options): static
    {
        $this->fieldInputOptions = array_merge($this->fieldInputOptions, $options);

        return $this;
    }

    public function getCreatable(): ?bool
    {
        return $this->creatable;
    }

    public function getUpdatable(): ?bool
    {
        return $this->updatable;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * Effective writability for a given operation: honours readonly() plus the
     * finer creatable/updatable gates (unset = allowed).
     */
    public function isWritableForOperation(string $op): bool
    {
        if (!$this->isWritable()) {
            return false;
        }

        if ($op === SchemaInterface::OP_CREATE) {
            return $this->creatable !== false;
        }

        if ($op === SchemaInterface::OP_UPDATE || $op === SchemaInterface::OP_PATCH) {
            return $this->updatable !== false;
        }

        return true;
    }

    /**
     * Set flags + custom attributes to merge into the column node (omit unset).
     *
     * @return array<string, mixed>
     */
    protected function columnAttributes(): array
    {
        $attributes = [];
        if ($this->hiddenInTable !== null) {
            $attributes['hiddenInTable'] = $this->hiddenInTable;
        }
        if ($this->hiddenInForm !== null) {
            $attributes['hiddenInForm'] = $this->hiddenInForm;
        }
        if ($this->hiddenInView !== null) {
            $attributes['hiddenInView'] = $this->hiddenInView;
        }
        if ($this->creatable !== null) {
            $attributes['creatable'] = $this->creatable;
        }
        if ($this->updatable !== null) {
            $attributes['updatable'] = $this->updatable;
        }

        return array_merge($attributes, $this->extra);
    }

    /**
     * Build a fieldInput node with the given widget type, plus position and extra options.
     *
     * @param array<string, mixed> $options base options (e.g. colspan, valueKey)
     *
     * @return array<string, mixed>
     */
    protected function buildFieldInput(string $type, array $options = []): array
    {
        $fieldInput = ['type' => $type];
        if ($this->position !== null) {
            $fieldInput['position'] = $this->position;
        }
        $fieldInput['options'] = array_merge($options, $this->fieldInputOptions);

        return $fieldInput;
    }
}
