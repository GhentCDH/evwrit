<?php

namespace App\Model;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class TextTranslation
 *
 * @property int $text_selection_id
 * @property int $text_id
 * @property string $text
 * @property string $text_edited
 * @property int $line_number_start
 * @property int $line_number_end
 * @property int $selection_start
 * @property int $selection_end
 * @property int $selection_length
 * @property Text $sourceText
 * @package App\Model
 */
class TextSelection extends AbstractModel
{
    /**
     * @return BelongsTo|Text
     * @throws ReflectionException
     */
    public function sourceText(): belongsTo
    {
        return $this->belongsTo(Text::class);
    }
}
