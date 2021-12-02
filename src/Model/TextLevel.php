<?php

namespace App\Model;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class TextTranslation
 *
 * @property int $text_level_id
 * @property int $text_id
 * @property int $number
 * @property string $type
 * @package App\Model
 */
class TextLevel extends AbstractModel
{
    /**
     * @return BelongsTo|Text
     * @throws ReflectionException
     */
    public function text(): belongsTo
    {
        return $this->belongsTo(Text::class);
    }
}
