<?php
namespace App\Model;


use Illuminate\Database\Eloquent\Model;
use function Symfony\Component\String\u;

class BaseModel extends Model
{
    protected $primaryKey; // = 'text_id';
    protected $table; // = 'text';

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = u((new \ReflectionClass($this))->getShortName())->snake();
        $this->primaryKey = $this->table . '_id';
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        return parent::belongsTo($related, $foreignKey, $ownerKey, $relation);
    }

    public function belongsToMany($related, $table = NULL, $foreignPivotKey = NULL, $relatedPivotKey = NULL, $parentKey = NULL, $relatedKey = NULL, $relation = NULL)
    {
        if (is_null($table)) {
            $related_table = u((new \ReflectionClass($related))->getShortName())->snake();
            $table = $this->table . '__' . $related_table;
        }

        if (is_null($foreignPivotKey)) {
            $foreignPivotKey = $this->getKeyName();
        }

        if (is_null($relatedPivotKey)) {
            $related_table = u((new \ReflectionClass($related))->getShortName())->snake();
            $relatedPivotKey = $related_table.'_id';
        }
        return parent::belongsToMany($related, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relation);
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