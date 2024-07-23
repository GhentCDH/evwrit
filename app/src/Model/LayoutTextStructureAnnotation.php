<?php

namespace App\Model;

use App\Model\Lookup\LayoutTextStructurePart;
use App\Model\Lookup\TextStructureAlignment;
use App\Model\Lookup\TextStructureAnnotationType;
use App\Model\Lookup\TextStructureIndentation;
use App\Model\Lookup\TextStructureLectionalSigns;
use App\Model\Lookup\TextStructureLineation;
use App\Model\Lookup\TextStructureAnnotationSubtype;
use App\Model\Lookup\TextStructureOrientation;
use App\Model\Lookup\TextStructurePagination;
use App\Model\Lookup\TextStructureSeparation;
use App\Model\Lookup\TextStructureSpacing;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class HandshiftAnnotation
 *
 * @property int $layout_text_structure_annotation_id
 * @property int $text_selection_id
 *
 * @property LayoutTextStructure layoutTextStructure
 * @property string partNumber*
 * @package App\Model
 */
class LayoutTextStructureAnnotation extends AbstractAnnotationModel
{
    protected $with = ['textSelection',
//        'textSelection.sourceText',
        'type', 'subtype',
        'layoutTextStructure', 'layoutTextStructure.part',
        'spacing', 'separation', 'orientation', 'alignment', 'indentation', 'lectionalSigns', 'lineation', 'pagination'];

    public function getAnnotationType(): string
    {
        return 'ltsa';
    }

    /**
     * @return BelongsTo|LayoutTextStructure
     * @throws ReflectionException
     */
    public function layoutTextStructure(): belongsTo
    {
        return $this->belongsTo(LayoutTextStructure::class);
    }

    /**
     * @return LayoutTextStructurePart
     */
    public function getPartAttribute(): ?LayoutTextStructurePart
    {
        return $this->layoutTextStructure->part;
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public function getPartNumberAttribute(): ?string
    {
        return $this->layoutTextStructure->partNumber;
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

    /**
     * @return BelongsTo|TextStructureSpacing
     * @throws ReflectionException
     */
    public function spacing(): belongsTo
    {
        return $this->belongsTo(TextStructureSpacing::class);
    }

    /**
     * @return BelongsTo|TextStructureSeparation
     * @throws ReflectionException
     */
    public function separation(): belongsTo
    {
        return $this->belongsTo(TextStructureSeparation::class);
    }

    /**
     * @return BelongsTo|TextStructureOrientation
     * @throws ReflectionException
     */
    public function orientation(): belongsTo
    {
        return $this->belongsTo(TextStructureOrientation::class);
    }

    /**
     * @return BelongsTo|TextStructureAlignment
     * @throws ReflectionException
     */
    public function alignment(): belongsTo
    {
        return $this->belongsTo(TextStructureAlignment::class);
    }

    /**
     * @return BelongsTo|TextStructureIndentation
     * @throws ReflectionException
     */
    public function indentation(): belongsTo
    {
        return $this->belongsTo(TextStructureIndentation::class);
    }

    /**
     * @return BelongsTo|TextStructureLectionalSigns
     * @throws ReflectionException
     */
    public function lectionalSigns(): belongsTo
    {
        return $this->belongsTo(TextStructureLectionalSigns::class);
    }

    /**
     * @return BelongsTo|TextStructureLineation
     * @throws ReflectionException
     */
    public function lineation(): belongsTo
    {
        return $this->belongsTo(TextStructureLineation::class);
    }

    /**
     * @return BelongsTo|TextStructurePagination
     * @throws ReflectionException
     */
    public function pagination(): belongsTo
    {
        return $this->belongsTo(TextStructurePagination::class);
    }

}
