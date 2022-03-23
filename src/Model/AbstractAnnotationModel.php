<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * @property int $text_selection_id
 * @property TextSelection textSelection
 */
abstract class AbstractAnnotationModel extends AbstractModel
{
    abstract function getAnnotationType(): string;

    /**
     * @return BelongsTo|TextSelection
     * @throws ReflectionException
     */
    public function textSelection(): belongsTo
    {
        return $this->belongsTo(TextSelection::class);
    }

    /**
     * @return Text|BelongsTo
     * @throws ReflectionException
     */
    public function sourceText() {
        return $this->textSelection->sourceText;
    }
}