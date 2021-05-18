<?php

namespace App\Model;


use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Script
 *
 * @package App\Model
 */
class Location extends IdNameModel
{

    /**
     * @return HasMany|Collection|TextLocation[]
     */
    public function textLocations(): HasMany
    {
        return $this->hasMany(TextLocation::class);
    }
}
