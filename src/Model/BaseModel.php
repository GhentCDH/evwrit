<?php
namespace App\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use ReflectionClass;
use ReflectionException;
use function Symfony\Component\String\u;

class BaseModel extends Model
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
    public function belongsTo(string $related, string $foreignKey = null, string $ownerKey = null, $relation = null): BelongsTo
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
    public function belongsToMany(string $related, string $table = NULL, string $foreignPivotKey = NULL, string $relatedPivotKey = NULL, $parentKey = NULL, $relatedKey = NULL, $relation = NULL): BelongsToMany
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
    public function hasMany(string $related, string $foreignKey = null, string $localKey = null): HasMany
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
    public function hasOne(string $related, string $foreignKey = null, string $localKey = null): HasOne
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

}