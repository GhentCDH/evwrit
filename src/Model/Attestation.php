<?php

namespace App\Model;

use App\Model\Lookup\Age;
use App\Model\Lookup\AttestationHypertype;
use App\Model\Lookup\Domicile;
use App\Model\Lookup\Education;
use App\Model\Lookup\GraphType;
use App\Model\Lookup\HonorificEpithet;
use App\Model\Lookup\LocationType;
use App\Model\Lookup\Occupation;
use App\Model\Lookup\Role;
use App\Model\Lookup\SocialRank;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use ReflectionException;

/**
 * Class Attestation
 *
 * @property int $attestation_id
 * @property string remark_social_background
 * @property string comment
 * @property bool occupation_is_overt
 * @property bool occupation_is_former
 * @property bool occupation_is_collective
 *
 * @property AncientPerson ancientPerson
 * @property Level level
 * @property Age $age
 * @property Education $education
 * @property GraphType $graphType
 * @property Domicile $domicile
 * @property LocationType $locationType
 * @property AttestationHypertype $attestationHypertype
 * @property Role[] roles
 * @property Occupation[] occupations
 * @property SocialRank[] $socialRanks
 * @property HonorificEpithet[] $honorificEpithets
 * @package App\Model
 */
class Attestation extends AbstractModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * @return BelongsTo|Level
     * @throws ReflectionException
     */
    public function level(): belongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * @return BelongsTo|AncientPerson
     * @throws ReflectionException
     */
    public function ancientPerson(): BelongsTo
    {
        return $this->belongsTo(AncientPerson::class);
    }

    /* many 2 many */

    /**
     * @return BelongsToMany|Role
     * @throws ReflectionException
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @return BelongsToMany|Occupation
     * @throws ReflectionException
     */
    public function occupations(): BelongsToMany
    {
        return $this->belongsToMany(Occupation::class);
    }

    /**
     * @return BelongsToMany|SocialRank
     * @throws ReflectionException
     */
    public function socialRanks(): BelongsToMany
    {
        return $this->belongsToMany(SocialRank::class);
    }

    /**
     * @return BelongsToMany|HonorificEpithet
     * @throws ReflectionException
     */
    public function honorificEpithets(): BelongsToMany
    {
        return $this->belongsToMany(HonorificEpithet::class);
    }

    /* properties */

    public function age(): BelongsTo
    {
        return $this->belongsTo( Age::class);
    }

    public function education(): BelongsTo
    {
        return $this->belongsTo( Education::class);
    }

    public function graphType(): BelongsTo
    {
        return $this->belongsTo( GraphType::class);
    }

    public function domicile(): BelongsTo
    {
        return $this->belongsTo( Domicile::class);
    }

    public function locationType(): BelongsTo
    {
        return $this->belongsTo( LocationType::class);
    }

    public function attestationHypertype(): BelongsTo
    {
        return $this->belongsTo( AttestationHypertype::class);
    }

}
