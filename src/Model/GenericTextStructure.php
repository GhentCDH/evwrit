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
 *
 * @property TextSelection $textSelection
 * @property GenericTextStructurePart part
 * @property GenericTextStructureComponents components
 * @property TextLevel textLevel
 * @package App\Model
 */
class GenericTextStructure extends AbstractModel
{
    /**
     * @return BelongsTo|TextSelection
     * @throws ReflectionException
     */
    public function textSelection(): belongsTo
    {
        return $this->belongsTo(TextSelection::class);
    }

    /**
     * @return BelongsTo|TextLevel
     * @throws ReflectionException
     */
    public function textLevel(): belongsTo
    {
        return $this->belongsTo(TextLevel::class);
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
     * @return int
     */
    public function partNumber(): ?int
    {
        return $this->generic_text_structure_part_number;
    }

    /**
     * @return BelongsTo|GenericTextStructureComponents
     * @throws ReflectionException
     */
    public function components(): belongsTo
    {
        return $this->belongsTo(GenericTextStructureComponents::class);
    }

    /**
     * @return Text|BelongsTo
     * @throws ReflectionException
     */
    public function sourceText() {
        return $this->textSelection->sourceText;
    }

}
