<?php

namespace App\Model;

use App\Model\Lookup\TextStructureAnnotationType;
use App\Model\Lookup\TextStructureAnnotationSubtype;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class HandshiftAnnotation
 *
 * @property int $generic_text_structure_annotation_id
 * @property int $text_selection_id
 * @package App\Model
 */
class GenericTextStructureAnnotation extends AbstractAnnotationModel
{
    protected $with = ['textSelection', 'textSelection.sourceText', 'type', 'subtype'];

    public function getAnnotationType(): string
    {
        return 'gts';
    }

    /**
     * @return BelongsTo|TextStructureAnnotationType
     * @throws ReflectionException
     */
    public function type(): belongsTo
    {
        return $this->belongsTo(TextStructureAnnotationType::class);
    }

    /**
     * @return BelongsTo|TextStructureAnnotationSubtype
     * @throws ReflectionException
     */
    public function subtype(): belongsTo
    {
        return $this->belongsTo(TextStructureAnnotationSubtype::class);
    }

}
