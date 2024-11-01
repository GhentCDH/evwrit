<?php

namespace App\Model;


use App\Model\Lookup\GenericTextStructureComponents;
use App\Model\Lookup\GenericTextStructurePart;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class GenericTextStructure
 *
 * @property int $generic_text_structure_id
 * @property int $text_selection_id
 * @property int $text_level_id
 * @property int $generic_text_structure_part_id
 * @property int $generic_text_structure_part_number
 * @property int $text_structure_components_id
 * @property string $comment
 * @property string $preservationStatus
 *
 * @property TextSelection $textSelection
 * @property AnnotationOverride override
 * @property GenericTextStructurePart part
 * @property GenericTextStructureComponents components
 * @property Level textLevel
 * @property string partNumber*
 * @package App\Model
 */
class GenericTextStructure extends AbstractModel
{
    protected $with = ['textSelection',
//        'textSelection.sourceText',
        'part', 'textLevel'];

    /**
     * @return BelongsTo|TextSelection
     * @throws ReflectionException
     */
    public function textSelection(): belongsTo
    {
        return $this->belongsTo(TextSelection::class);
    }

    /**
     * @return BelongsTo|Level
     * @throws ReflectionException
     */
    public function textLevel(): belongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * @return BelongsTo|GenericTextStructurePart
     * @throws ReflectionException
     */
    public function part(): belongsTo
    {
        return $this->belongsTo(GenericTextStructurePart::class);
    }

    /**
     * @return string
     */
    public function getPartNumberAttribute(): ?string
    {
        return $this->generic_text_structure_part_number;
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
