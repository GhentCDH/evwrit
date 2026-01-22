<?php

namespace App\Model;


use Illuminate\Database\Eloquent\Relations\MorphTo;
use ReflectionException;

/**
 * Class TextSelectionOverride
 *
 * @property int $annotation_override_id
 * @property int $annotation_id
 * @property string $annotation_type
 * @property ?int $selection_start
 * @property ?int $selection_end
 * @property ?int $selection_length
 * @property bool $is_deleted
 * @package App\Model
 */
class AnnotationOverride extends AbstractModel
{
    protected $fillable = ['annotation_id', 'annotation_type', 'selection_start', 'selection_end', 'selection_length', 'is_deleted'];

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    /**
     * @return MorphTo
     * @throws ReflectionException
     */
    public function annotation(): morphTo
    {
        return $this->morphTo();
    }
}
