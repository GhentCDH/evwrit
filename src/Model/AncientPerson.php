<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use ReflectionException;
use function Symfony\Component\String\u;

/**
 * Class AncientPerson
 *
 * @property int $ancient_person_id
 * @property string $name
 * @property string $alias
 * @property string $patronymic
 * @property int $tm_id
 * @property int $gender_id
 * @package App\Model
 */
class AncientPerson extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['ancient_person_id','name','alias','patronymic','tm_id','gender_id'];

    /**
     * @return BelongsTo|Gender
     * @throws ReflectionException
     */
    public function gender(): belongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }

    /**
     * @return int
     */
    public function getTmId(): ?int
    {
        return $this->tm_id;
    }

    /**
     * @return int
     */
    public function getGenderId(): ?int
    {
        return $this->gender_id;
    }

}
