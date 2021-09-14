<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class OrthographyLexisAnnotation
 *
 * @property int $orthography_lexis_annotation_id
 * @property int $text_selection_id

 * @package App\Model
 */
class OrthographyLexisAnnotation extends BaseModel
{
    /**
     * @return BelongsTo|TextSelection
     * @throws ReflectionException
     */
    public function textSelection(): belongsTo
    {
        return $this->belongsTo(TextSelection::class);
    }

}
