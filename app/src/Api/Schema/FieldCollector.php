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
