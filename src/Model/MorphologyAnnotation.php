<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class MorphologyAnnotation
 *
 * @property int $morphology_annotation_id
 * @property int $text_selection_id
 * @property int $annotation_standard_form_morphology_id
 * @property int $annotation_type_morphology_id
 * @property int $annotation_subype_morphology_id
 * @property int $annotation_wordclass_morphology_id
 * @property int $annotation_formulaicity_morphology_id
 * @property int $annotation_prescription_morphology_id
 * @property int $annotation_proscription_morphology_id
 * @property int $annotation_position_in_word_morphology_id
 * @property int $annotation_identifier_morphology_id
 * @package App\Model
 */
class MorphologyAnnotation extends AbstractAnnotationModel
{
    public function getAnnotationType(): string
    {
        return 'morphology';
    }

    public function standardForm(): belongsTo
    {
        return $this->belongsTo(AnnotationStandardFormMorphology::class);
    }

    public function type(): belongsTo
    {
        return $this->belongsTo(AnnotationTypeMorphology::class);
    }

    public function subtype(): belongsTo
    {
        return $this->belongsTo(AnnotationSubtypeMorphology::class);
    }

    public function wordclass(): belongsTo
    {
        return $this->belongsTo(AnnotationWordclassMorphology::class);
    }

    public function formulaicity(): belongsTo
    {
        return $this->belongsTo(AnnotationFormulaicityMorphology::class);
    }

    public function prescription(): belongsTo
    {
        return $this->belongsTo(AnnotationPrescriptionMorphology::class);
    }

    public function proscription(): belongsTo
    {
        return $this->belongsTo(AnnotationProscriptionMorphology::class);
    }

    public function positionInWord(): belongsTo
    {
        return $this->belongsTo(AnnotationPositionInWordMorphology::class);
    }

    public function identifier(): belongsTo
    {
        return $this->belongsTo(AnnotationIdentifierMorphology::class);
    }
}
