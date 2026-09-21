<?php

namespace App\Api\Schema;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Collects all tagged model schemas and resolves them by key.
 */
class SchemaRegistry
{
    /** @var array<string, SchemaInterface> */
    private array $schemas = [];

    /** @var array<class-string, string> model class => schema key (first wins) */
    private array $byModel = [];

    /**
     * @param iterable<SchemaInterface> $schemas
     */
    public function __construct(iterable $schemas)
    {
        foreach ($schemas as $schema) {
            $this->schemas[$schema->getKey()] = $schema;

            $model = $schema->getModelClass();
            if (!isset($this->byModel[$model])) {
                $this->byModel[$model] = $schema->getKey();
            }
        }
    }

    public function has(string $key): bool
    {
        return isset($this->schemas[$key]);
    }

    public function hasModel(string $modelClass): bool
    {
        return isset($this->byModel[$modelClass]);
    }

    /**
     * The schema key that exposes the given model class, or null if none does.
     *
     * @param class-string $modelClass
     */
    public function getKeyForModel(string $modelClass): ?string
    {
        return $this->byModel[$modelClass] ?? null;
    }

    public function get(string $key): SchemaInterface
    {
        if (!isset($this->schemas[$key])) {
            throw new NotFoundHttpException(sprintf('Unknown model "%s".', $key));
        }

        return $this->schemas[$key];
    }

    /**
     * @return array<string, SchemaInterface>
     */
    public function all(): array
    {
        return $this->schemas;
    }
}
