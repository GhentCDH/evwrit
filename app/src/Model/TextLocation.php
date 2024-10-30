<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

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
class TextLocation extends AbstractModel
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
