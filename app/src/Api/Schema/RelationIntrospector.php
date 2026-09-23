<?php

namespace App\Api\Schema;

use App\Model\AbstractModel;
use App\Model\IdNameModelInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Derives foreign-key metadata for a belongsTo relation by inspecting the live
 * Eloquent relation, so a schema can declare a relation field in one line.
 */
class RelationIntrospector
{
    /**
     * @param class-string<AbstractModel> $modelClass
     *
     * @return array{foreignKey: string, relatedModel: class-string<AbstractModel>, labelAttribute: string}
     */
    public function inspect(string $modelClass, string $relationMethod): array
    {
        $model = new $modelClass();

        if (!method_exists($model, $relationMethod)) {
            throw new InvalidArgumentException(sprintf(
                'Model %s has no relation method "%s".',
                $modelClass,
                $relationMethod
            ));
        }

        $relation = $model->{$relationMethod}();

        if (!$relation instanceof BelongsTo) {
            throw new InvalidArgumentException(sprintf(
                'Relation %s::%s() must be a belongsTo relation, got %s.',
                $modelClass,
                $relationMethod,
                get_debug_type($relation)
            ));
        }

        $related = $relation->getRelated();

        return [
            'foreignKey' => $relation->getForeignKeyName(),
            'relatedModel' => $related::class,
            'labelAttribute' => $related instanceof IdNameModelInterface ? 'name' : 'name',
        ];
    }

    /**
     * Discovers the belongsTo relation methods on a model that point at an id/name lookup
     * (a related model implementing {@see IdNameModelInterface}), so an annotation schema
     * can expose its lookup columns without a hand-maintained list.
     *
     * Relations are found by return-type reflection (no query is run); each candidate is
     * then built to confirm the related model is a lookup. Non-lookup belongsTo relations
     * (e.g. the text selection) and other relation kinds are excluded.
     *
     * @param class-string<AbstractModel> $modelClass
     *
     * @return string[] relation method names, in declaration order
     */
    public function discoverLookupRelations(string $modelClass): array
    {
        $model = new $modelClass();
        $relations = [];

        foreach ((new ReflectionClass($modelClass))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic() || $method->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            $returnType = $method->getReturnType();
            if (!$returnType instanceof ReflectionNamedType
                || $returnType->isBuiltin()
                || !is_a($returnType->getName(), BelongsTo::class, true)) {
                continue;
            }

            $relation = $model->{$method->getName()}();
            if ($relation instanceof BelongsTo && $relation->getRelated() instanceof IdNameModelInterface) {
                $relations[] = $method->getName();
            }
        }

        return $relations;
    }
}
