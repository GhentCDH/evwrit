<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use ReflectionException;
use function Symfony\Component\String\u;

/**
 * Class Attestation
 *
 * @property int $ancient_person_id
 * @property string $name
 * @property string $alias
 * @property string $patronymic
 * @property int $tm_id
 * @property int $gender_id
 * @package App\Model
 */
class Attestation extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * @return BelongsTo|Text
     * @throws ReflectionException
     */
    public function text(): belongsTo
    {
        return $this->belongsTo(Text::class);
    }

    /**
     * @return BelongsTo|AncientPerson
     * @throws ReflectionException
     */
    public function ancientPerson(): belongsTo
    {
        return $this->belongsTo(AncientPerson::class);
    }

    /**
     * @return BelongsToMany|Role
     * @throws ReflectionException
     */
    public function roles(): belongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @return BelongsToMany|Occupation
     * @throws ReflectionException
     */
    public function occupations(): belongsToMany
    {
        return $this->belongsToMany(Occupation::class);
    }

    /**
     * @return BelongsToMany|SocialRank
     * @throws ReflectionException
     */
    public function socialRanks(): belongsToMany
    {
        return $this->belongsToMany(SocialRank::class);
    }

    /**
     * @return BelongsToMany|HonorificEpithet
     * @throws ReflectionException
     */
    public function honorificEpithets(): belongsToMany
    {
        return $this->belongsToMany(HonorificEpithet::class);
    }


}
