<?php

namespace App\Model;


use App\Model\Lookup\LayoutTextStructurePart;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class LayoutTextStructure
 *
 * @property int $layout_text_structure_id
 * @property int $text_selection_id
 * @property int $layout_text_structure_part_id
 * @property string $preservationStatus
 *
 * @property TextSelection textSelection
 * @property LayoutTextStructurePart part
 * @property string partNumber*
 * @property AnnotationOverride override
 * @package App\Model
 */
class LayoutTextStructure extends AbstractModel
{
    protected $with = ['textSelection',
//        'textSelection.sourceText',
        'part'];

    /**
     * @return BelongsTo|TextSelection
     * @throws ReflectionException
     */
    public function textSelection(): belongsTo
    {
        return $this->belongsTo(TextSelection::class);
    }

    /**
     * @return BelongsTo|LayoutTextStructurePart
     * @throws ReflectionException
     */
    public function part(): belongsTo
    {
        return $this->belongsTo(LayoutTextStructurePart::class);
    }

    /**
     * @return string
     */
    public function getPartNumberAttribute(): ?string
    {
        return $this->layout_text_structure_part_number;
    }

    /**
     * @return Text|BelongsTo
     * @throws ReflectionException
     */
    public function sourceText()
    {
        return $this->textSelection->sourceText;
    }

    public function override()
    {
        return $this->morphOne(AnnotationOverride::class, 'annotation');
    }

}
