<?php

namespace App\Api\Schema;

use App\Model\AbstractModel;
use App\Model\IdNameModelInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

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
}
