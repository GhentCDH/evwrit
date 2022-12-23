<?php
namespace App\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use function Symfony\Component\String\u;

abstract class AbstractModel extends Model
{
    protected $primaryKey;
    protected $table;

    public $timestamps = false;

    /**
     * BaseModel constructor.
     * - snake case table names
     * - tablename_id primary key
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if ( !$this->table ) {
            $this->table = u((new ReflectionClass($this))->getShortName())->snake();
        }
        if ( !$this->primaryKey ) {
            $this->primaryKey = $this->table . '_id';
        }
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table ?? Str::snake(class_basename($this));
    }

    /**
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey(): string
    {
        return $this->getKeyName();
    }

    /**
     * belongsTo
     * - foreign key name = primary key name or related table
     *
     * @param string $related
     * @param string|null $foreignKey
     * @param string|null $ownerKey
     * @param null $relation
     * @return BelongsTo
     * @throws ReflectionException
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null): BelongsTo
    {
        $related_table = u((new ReflectionClass($related))->getShortName())->snake();
        $related_pk = $related_table.'_id';

        if (is_null($foreignKey)) {
            $foreignKey = $related_pk;
        }

        if (is_null($ownerKey)) {
            $ownerKey = $related_pk;
        }

        return parent::belongsTo($related, $foreignKey, $ownerKey, $relation);
    }

    /**
     * belongsToMany
     * - foreign key names = primary key names or related tables
     * - join table name = table_name__related_table_name
     *
     * @param string $related
     * @param string|null $table
     * @param string|null $foreignPivotKey
     * @param string|null $relatedPivotKey
     * @param null $parentKey
     * @param null $relatedKey
     * @param null $relation
     * @return BelongsToMany
     * @throws ReflectionException
     */
    public function belongsToMany($related, $table = NULL, $foreignPivotKey = NULL, $relatedPivotKey = NULL, $parentKey = NULL, $relatedKey = NULL, $relation = NULL): BelongsToMany
    {
        if (is_null($relation)) {
            $relation = $this->guessBelongsToManyRelation();
        }
        $instance = $this->newRelatedInstance($related);

        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        $table = $table ?: $this->getTable().'__'.$instance->getTable();

        return parent::belongsToMany($related, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relation);
    }

    /**
     * hasMany
     * - foreign key name = primary key name of current table
     *
     * @param string $related
     * @param string|null $foreignKey
     * @param string|null $localKey
     * @return HasMany
     */
    public function hasMany($related, $foreignKey = null, $localKey = null): HasMany
    {
        if (is_null($foreignKey)) {
            $foreignKey = $this->getKeyName();
        }

        if (is_null($localKey)) {
            $localKey = $this->getKeyName();
        }
        return parent::hasMany($related, $foreignKey, $localKey);
    }

    /**
     * @param string $related
     * @param string|null $foreignKey
     * @param string|null $localKey
     * @return HasOne
     */
    public function hasOne($related, $foreignKey = null, $localKey = null): HasOne
    {
        if (is_null($foreignKey)) {
            $foreignKey = $this->getKeyName();
        }

        if (is_null($localKey)) {
            $localKey = $this->getKeyName();
        }
        return parent::hasOne($related, $foreignKey, $localKey);
    }

    /**
     * Return primary key
     *
     * @return mixed
     */
    public function getId(): int
    {
        return $this->getKey();
    }

    /**
     * @param  string  $related
     * @param  string  $through
     * @param  string|null  $firstKey
     * @param  string|null  $secondKey
     * @param  string|null  $localKey
     * @param  string|null  $secondLocalKey
     * @return HasManyThrough
     */
    public function hasManyThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null, $secondLocalKey = null): HasManyThrough
    {
        $through_table = u((new ReflectionClass($through))->getShortName())->snake();
        $through_pk = $through_table.'_id';

        if (is_null($localKey)) {
            $localKey = $this->getKeyName();
        }

        return parent::hasManyThrough($related, $through, $localKey, $through_pk, $localKey, $through_pk);
    }
}