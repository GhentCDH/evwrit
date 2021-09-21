<?php

namespace App\Model;

use App\Model\Lookup\AnnotationFormulaicityLexis;
use App\Model\Lookup\AnnotationIdentifierLexis;
use App\Model\Lookup\AnnotationPositionInWordLexis;
use App\Model\Lookup\AnnotationPrescriptionLexis;
use App\Model\Lookup\AnnotationProscriptionLexis;
use App\Model\Lookup\AnnotationStandardFormLexis;
use App\Model\Lookup\AnnotationSubtypeLexis;
use App\Model\Lookup\AnnotationTypeLexis;
use App\Model\Lookup\AnnotationWordclassLexis;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OrthographyAnnotation
 *
 * @property int $lexis_annotation_id
 * @property int $text_selection_id
 * @property int $annotation_standard_form_lexis_id
 * @property int $annotation_type_lexis_id
 * @property int $annotation_subtype_lexis_id
 * @property int $annotation_wordclass_lexis_id
 * @property int $annotation_formulaicity_lexis_id
 * @property int $annotation_prescription_lexis_id
 * @property int $annotation_proscription_lexis_id
 * @property int $annotation_position_in_word_lexis_id
 * @property int $annotation_identifier_lexis_id
 * @package App\Model
 */
class LexisAnnotation extends AbstractAnnotationModel
{
    public function getAnnotationType(): string
    {
        return 'lexis';
    }

    public function standardForm(): belongsTo
    {
        return $this->belongsTo(AnnotationStandardFormLexis::class);
    }

    public function type(): belongsTo
    {
        return $this->belongsTo(AnnotationTypeLexis::class);
    }

    public function subtype(): belongsTo
    {
        return $this->belongsTo(AnnotationSubtypeLexis::class);
    }

    public function wordclass(): belongsTo
    {
        return $this->belongsTo(AnnotationWordclassLexis::class);
    }

    public function formulaicity(): belongsTo
    {
        return $this->belongsTo(AnnotationFormulaicityLexis::class);
    }

    public function prescription(): belongsTo
    {
        return $this->belongsTo(AnnotationPrescriptionLexis::class);
    }

    public function proscription(): belongsTo
    {
        return $this->belongsTo(AnnotationProscriptionLexis::class);
    }

    public function positionInWord(): belongsTo
    {
        return $this->belongsTo(AnnotationPositionInWordLexis::class);
    }

    public function identifier(): belongsTo
    {
        return $this->belongsTo(AnnotationIdentifierLexis::class);
    }

}
