<?php

namespace App\Model;

use App\Model\Lookup\GenericTextStructurePart;
use App\Model\Lookup\TextStructureAnnotationType;
use App\Model\Lookup\TextStructureAnnotationSubtype;
use App\Model\Lookup\TextStructureAttachedTo;
use App\Model\Lookup\TextStructureAttachmentType;
use App\Model\Lookup\TextStructureInformationStatus;
use App\Model\Lookup\TextStructureSpeechAct;
use App\Model\Lookup\TextStructureAlignment;
use App\Model\Lookup\TextStructureStandardForm;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use ReflectionException;

/**
 * Class HandshiftAnnotation
 *
 * @property int $generic_text_structure_annotation_id
 * @property int $text_selection_id
 *
 * @property GenericTextStructure genericTextStructure
 * @property string partNumber
 * @package App\Model
 */
class GenericTextStructureAnnotation extends AbstractAnnotationModel
{
    protected $with = ['textSelection', 'textSelection.sourceText', 'type', 'subtype','genericTextStructure', 'genericTextStructure.part','genericTextStructure.textLevel',
        'genericTextStructure', 'genericTextStructure.part', 'genericTextStructure.textLevel',
        'standardForm', 'attachedTo', 'attachmentType','speechAct','informationStatus',];

    public function getAnnotationType(): string
    {
        return 'gtsa';
    }

    /**
     * @return BelongsTo|GenericTextStructure
     * @throws ReflectionException
     */
    public function genericTextStructure(): belongsTo
    {
        return $this->belongsTo(GenericTextStructure::class);
    }

    /**
     * @return GenericTextStructurePart
     */
    public function getPartAttribute(): ?GenericTextStructurePart
    {
        return $this->genericTextStructure->part;
    }

    /**
     * @return Level
     */
    public function getTextLevelAttribute(): ?Level
    {
        return $this->genericTextStructure->textLevel;
    }

    /**
     * @return string
     */
    public function getPartNumberAttribute(): ?string
    {
        return $this->genericTextStructure->partNumber;
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
     * @return BelongsTo|TextStructureAlignment
     * @throws ReflectionException
     */
    public function standardForm(): belongsTo
    {
        return $this->belongsTo(TextStructureStandardForm::class);
    }

    /**
     * @return BelongsTo|TextStructureAttachedTo
     * @throws ReflectionException
     */
    public function attachedTo(): belongsTo
    {
        return $this->belongsTo(TextStructureAttachedTo::class);
    }

    /**
     * @return BelongsTo|TextStructureAttachmentType
     * @throws ReflectionException
     */
    public function attachmentType(): belongsTo
    {
        return $this->belongsTo(TextStructureAttachmentType::class);
    }

    /**
     * @return BelongsTo|TextStructureSpeechAct
     * @throws ReflectionException
     */
    public function speechAct(): belongsTo
    {
        return $this->belongsTo(TextStructureSpeechAct::class);
    }

    /**
     * @return BelongsTo|TextStructureInformationStatus
     * @throws ReflectionException
     */
    public function informationStatus(): belongsTo
    {
        return $this->belongsTo(TextStructureInformationStatus::class);
    }

}
