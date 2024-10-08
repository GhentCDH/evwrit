<?php

namespace App\Model;

use App\Model\Lookup\AnnotationFormulaicityOrthography;
use App\Model\Lookup\AnnotationIdentifierOrthography;
use App\Model\Lookup\AnnotationPositionInWordOrthography;
use App\Model\Lookup\AnnotationPrescriptionOrthography;
use App\Model\Lookup\AnnotationProscriptionOrthography;
use App\Model\Lookup\AnnotationStandardFormOrthography;
use App\Model\Lookup\AnnotationSubtypeOrthography;
use App\Model\Lookup\AnnotationTypeOrthography;
use App\Model\Lookup\AnnotationWordclassOrthography;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OrthographyAnnotation
 *
 * @property int $orthography_annotation_id
 * @property int $text_selection_id
 * @property int $annotation_standard_form_orthography_id
 * @property int $annotation_type_orthography_id
 * @property int $annotation_subtype_orthography_id
 * @property int $annotation_wordclass_orthography_id
 * @property int $annotation_formulaicity_orthography_id
 * @property int $annotation_prescription_orthography_id
 * @property int $annotation_proscription_orthography_id
 * @property int $annotation_position_in_word_orthography_id
 * @property int $annotation_identifier_orthography_id
 * @package App\Model
 */
class OrthographyAnnotation extends AbstractAnnotationModel
{
    protected $with = ['textSelection',
//        'textSelection.sourceText',
        'standardForm','type','subtype','wordclass','formulaicity','prescription','proscription','positionInWord','identifier'];

    public function getAnnotationType(): string
    {
        return 'orthography';
    }

    public function standardForm(): belongsTo
    {
        return $this->belongsTo(AnnotationStandardFormOrthography::class);
    }

    public function type(): belongsTo
    {
        return $this->belongsTo(AnnotationTypeOrthography::class);
    }

    public function subtype(): belongsTo
    {
        return $this->belongsTo(AnnotationSubtypeOrthography::class);
    }

    public function wordclass(): belongsTo
    {
        return $this->belongsTo(AnnotationWordclassOrthography::class);
    }

    public function formulaicity(): belongsTo
    {
        return $this->belongsTo(AnnotationFormulaicityOrthography::class);
    }

    public function prescription(): belongsTo
    {
        return $this->belongsTo(AnnotationPrescriptionOrthography::class);
    }

    public function proscription(): belongsTo
    {
        return $this->belongsTo(AnnotationProscriptionOrthography::class);
    }

    public function positionInWord(): belongsTo
    {
        return $this->belongsTo(AnnotationPositionInWordOrthography::class);
    }

    public function identifier(): belongsTo
    {
        return $this->belongsTo(AnnotationIdentifierOrthography::class);
    }

}
