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
 * @property int $text__location_id
 * @property int $location_id
 * @property int $text_id
 * @property int $is_written
 * @property int $is_found
 * @package App\Model
 */
class TextLocation extends BaseModel
{
    protected $table = "text__location";

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
    public function location(): belongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
