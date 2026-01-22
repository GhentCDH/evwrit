<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use ReflectionException;

/**
 * @property int $text_selection_id
 * @property TextSelection textSelection
 * @property AnnotationOverride override
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
    public function sourceText(): Text
    {
        return $this->textSelection->sourceText;
    }

    public function override(): MorphOne
    {
        return $this->morphOne(AnnotationOverride::class, 'annotation');
    }
}