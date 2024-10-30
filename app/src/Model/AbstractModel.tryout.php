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

        if ( !$this->primaryKey ) {
            $this->primaryKey = $this->getTable() . '_id';
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
     * Get the joining table name for a many-to-many relation.
     *
     * @param  string  $related
     * @param  \Illuminate\Database\Eloquent\Model|null  $instance
     * @return string
     */
    public function joiningTable($related, $instance = null)
    {
        // The joining table name, by convention, is simply the snake cased models
        // sorted alphabetically and concatenated with an underscore, so we can
        // just sort the models and join them together to get the table name.
        $segments = [
            $this->joiningTableSegment(),
            $instance ? $instance->joiningTableSegment()
                : Str::snake(class_basename($related)),
        ];

        // Now that we have the model names in an array we can just sort them and
        // use the implode function to join them together with an underscores,
        // which is typically used by convention within the database system.
//        sort($segments);

        return strtolower(implode('__', $segments));
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
    public function _belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null): BelongsTo
    {
        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        $instance = $this->newRelatedInstance($related);

        // If no foreign key was supplied, we can use a backtrace to guess the proper
        // foreign key name by using the name of the relationship function, which
        // when combined with an "_id" should conventionally match the columns.
        if (is_null($foreignKey)) {
            $foreignKey = $instance->getKeyName();
        }

        // Once we have the foreign key names, we'll just create a new Eloquent query
        // for the related models and returns the relationship instance which will
        // actually be responsible for retrieving and hydrating every relations.
        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return $this->newBelongsTo(
            $instance->newQuery(), $this, $foreignKey, $ownerKey, $relation
        );
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
    public function _belongsToMany($related, $table = NULL, $foreignPivotKey = NULL, $relatedPivotKey = NULL, $parentKey = NULL, $relatedKey = NULL, $relation = NULL): BelongsToMany
    {
        $related_table = u((new ReflectionClass($related))->getShortName())->snake();
        $related_pk = $related_table.'_id';

        if (is_null($table)) {
            $table = $this->table . '__' . $related_table;
        }

        if (is_null($foreignPivotKey)) {
            $foreignPivotKey = $this->getKeyName();
        }

        if (is_null($relatedPivotKey)) {
            $relatedPivotKey = $related_pk;
        }
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