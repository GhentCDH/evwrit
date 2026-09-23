<?php

namespace App\Api\Schema;

use App\Api\Schema\Field\EmbeddedRelationField;
use App\Api\Schema\Field\FieldInterface;
use App\Api\Schema\Field\RelationField;
use App\Api\Schema\Field\ScalarField;
use App\Model\AbstractModel;

/**
 * Ordered, fluent collector of {@see FieldInterface} for one model. Reused both for
 * a schema's top-level fields and for an embedded relation's sub-schema.
 */
class FieldCollector
{
    /** @var FieldInterface[] */
    private array $fields = [];

    private bool $autowireDefault = false;

    /** @var array<string, bool> per-relation autowire overrides (name => enabled) */
    private array $autowireOverrides = [];

    /** @var array<int, array{when: string, fn: \Closure}> model write hooks for this collector's model */
    private array $writeHooks = [];

    /**
     * @param class-string<AbstractModel> $modelClass
     */
    public function __construct(
        private readonly string $modelClass,
        private readonly RelationIntrospector $introspector,
    ) {
    }

    /**
     * Enable/disable auto-filling autocomplete resource URLs from the schema registry.
     * With no $relations, sets the default for every relation in this collector; with a
     * list of relation names, sets overrides for just those (partial bulk enable/disable).
     * Per-field autowireResource()/noResource() still win. Order-independent (applied at
     * getFields()).
     *
     * @param string[]|null $relations
     */
    public function autowireResources(bool $enabled = true, ?array $relations = null): static
    {
        if ($relations === null) {
            $this->autowireDefault = $enabled;
        } else {
            foreach ($relations as $name) {
                $this->autowireOverrides[$name] = $enabled;
            }
        }

        return $this;
    }

    /**
     * Register a hook that runs on this collector's model before every save
     * (create/update/patch); the callable receives ($model, $op). Use it to derive or
     * enforce columns that aren't part of the public contract (e.g. a NOT NULL length).
     */
    public function onSave(callable $hook): static
    {
        $this->writeHooks[] = ['when' => 'save', 'fn' => \Closure::fromCallable($hook)];

        return $this;
    }

    /**
     * Register a hook that runs on this collector's model before save on CREATE only; the
     * callable receives ($model). Sugar for create-time defaults that must not be
     * re-applied on later updates.
     */
    public function onCreate(callable $hook): static
    {
        $this->writeHooks[] = ['when' => 'create', 'fn' => \Closure::fromCallable($hook)];

        return $this;
    }

    /**
     * @return array<int, array{when: string, fn: \Closure}>
     */
    public function getWriteHooks(): array
    {
        return $this->writeHooks;
    }

    /**
     * Run write hooks against a model for the given operation: 'save' hooks fire on every
     * op, 'create' hooks only on CREATE. Hooks are always invoked as ($model, $op); a
     * one-parameter closure simply ignores the extra argument.
     *
     * @param array<int, array{when: string, fn: \Closure}> $hooks
     */
    public static function runWriteHooks(array $hooks, AbstractModel $model, string $op): void
    {
        foreach ($hooks as $hook) {
            if ($hook['when'] === 'create' && $op !== SchemaInterface::OP_CREATE) {
                continue;
            }
            ($hook['fn'])($model, $op);
        }
    }

    public function string(string $column, ?string $label = null): ScalarField
    {
        return $this->add(new ScalarField($column, ScalarField::TYPE_STRING, $label));
    }

    public function text(string $column, ?string $label = null): ScalarField
    {
        return $this->add(new ScalarField($column, ScalarField::TYPE_TEXT, $label));
    }

    public function integer(string $column, ?string $label = null): ScalarField
    {
        return $this->add(new ScalarField($column, ScalarField::TYPE_INTEGER, $label));
    }

    public function number(string $column, ?string $label = null): ScalarField
    {
        return $this->add(new ScalarField($column, ScalarField::TYPE_NUMBER, $label));
    }

    public function boolean(string $column, ?string $label = null): ScalarField
    {
        return $this->add(new ScalarField($column, ScalarField::TYPE_BOOLEAN, $label));
    }

    public function date(string $column, ?string $label = null): ScalarField
    {
        return $this->add(new ScalarField($column, ScalarField::TYPE_DATE, $label));
    }

    /**
     * The model's primary key, exposed (by default) as "id" and marked
     * non-creatable/non-updatable (it is assigned by the database).
     */
    public function primaryKey(string $exposeAs = 'id'): ScalarField
    {
        $pk = (new $this->modelClass())->getKeyName();

        return $this->add(
            (new ScalarField($pk, ScalarField::TYPE_INTEGER, 'ID'))
                ->exposeAs($exposeAs)
                ->creatable(false)
                ->updatable(false)
                ->noFieldInput()
                ->hiddenInForm()
                ->attr('idField', true)
        );
    }

    /**
     * A select field. $values is a list of {value,label} option arrays, emitted under
     * fieldInput.options.values.
     *
     * @param array<int, array{value: mixed, label: string}> $values
     */
    public function select(string $column, array $values, ?string $label = null): ScalarField
    {
        return $this->add(
            (new ScalarField($column, ScalarField::TYPE_STRING, $label, 'select'))
                ->fieldInputOptions(['values' => $values])
        );
    }

    /**
     * One belongsTo relation as an {id,label} autocomplete, metadata reflection-derived.
     */
    public function relation(string $method, ?string $label = null): RelationField
    {
        $meta = $this->introspector->inspect($this->modelClass, $method);

        return $this->add(new RelationField(
            $method,
            $meta['foreignKey'],
            $meta['relatedModel'],
            $meta['labelAttribute'],
            $label,
        ));
    }

    /**
     * Bulk declare several reflection-derived belongsTo autocomplete relations.
     */
    public function relations(string ...$methods): static
    {
        foreach ($methods as $method) {
            $this->relation($method);
        }

        return $this;
    }

    /**
     * A rich belongsTo whose columns travel with the parent. The callback receives a
     * sub-collector (same helpers) that declares the related model's sub-schema.
     */
    public function embed(
        string $method,
        callable $subFields,
        string $mode = EmbeddedRelationField::MODE_NESTED,
    ): EmbeddedRelationField {
        $meta = $this->introspector->inspect($this->modelClass, $method);

        $sub = new self($meta['relatedModel'], $this->introspector);
        $subFields($sub);

        return $this->add(new EmbeddedRelationField(
            $method,
            $meta['foreignKey'],
            $meta['relatedModel'],
            $sub->getFields(),
            $mode,
            writeHooks: $sub->getWriteHooks(),
        ));
    }

    /**
     * @template T of FieldInterface
     *
     * @param T $field
     *
     * @return T
     */
    private function add(FieldInterface $field): FieldInterface
    {
        $this->fields[] = $field;

        return $field;
    }

    /**
     * @return FieldInterface[]
     */
    public function getFields(): array
    {
        // Finalize: relations that didn't set an explicit autowire inherit the collector
        // default (or a per-name override). Idempotent once set.
        foreach ($this->fields as $field) {
            if ($field instanceof RelationField && $field->getAutowire() === null) {
                $field->setAutowire($this->autowireOverrides[$field->getSource()] ?? $this->autowireDefault);
            }
        }

        return $this->fields;
    }
}
