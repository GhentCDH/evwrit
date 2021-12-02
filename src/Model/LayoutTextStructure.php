<?php

namespace App\Model;


use App\Model\Lookup\GenericTextStructureComponents;
use App\Model\Lookup\GenericTextStructurePart;
use App\Model\Lookup\LayoutTextStructurePart;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class LayoutTextStructure
 *
 * @property int $generic_text_structure_id
 * @property int $text_selection_id
 * @property int $text_level_id
 * @property int $layout_text_structure_part_id
 * @property int $text_structure_components_id
 * @property string $comment
 *
 * @property TextSelection textSelection
 * @property LayoutTextStructurePart part
 * @package App\Model
 */
class LayoutTextStructure extends AbstractModel
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
     * @return BelongsTo|LayoutTextStructurePart
     * @throws ReflectionException
     */
    public function part(): belongsTo
    {
        return $this->belongsTo(LayoutTextStructurePart::class);
    }

    /**
     * @return Text|BelongsTo
     * @throws ReflectionException
     */
    public function sourceText() {
        return $this->textSelection->sourceText;
    }


}
