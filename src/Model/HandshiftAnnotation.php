<?php

namespace App\Model;

use App\Model\Lookup\AnnotationAbbreviation;
use App\Model\Lookup\AnnotationAccentuation;
use App\Model\Lookup\AnnotationConnectivity;
use App\Model\Lookup\AnnotationCorrection;
use App\Model\Lookup\AnnotationCurvature;
use App\Model\Lookup\AnnotationDegreeOfFormality;
use App\Model\Lookup\AnnotationExpansion;
use App\Model\Lookup\AnnotationLineation;
use App\Model\Lookup\AnnotationOrientation;
use App\Model\Lookup\AnnotationPunctuation;
use App\Model\Lookup\AnnotationRegularity;
use App\Model\Lookup\AnnotationScriptType;
use App\Model\Lookup\AnnotationSlope;
use App\Model\Lookup\AnnotationWordSplitting;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class HandshiftAnnotation
 *
 * @property int $handshift_annotation_id
 * @property int $text_selection_id
 * @property int annotation_script_type_id
 * @property int annotation_degree_of_formality_id
 * @property int annotation_expansion_id
 * @property int annotation_slope_id
 * @property int annotation_curvature_id
 * @property int annotation_connectivity_id
 * @property int annotation_orientation_id
 * @property int annotation_regularity_id
 * @property int annotation_lineation_id
 * @property int annotation_punctuation_id
 * @property int annotation_accentuation_id
 * @property int annotation_word_splitting_id
 * @property int annotation_abbreviation_id
 * @property int annotation_correction_id
 * @property int internal_hand_num
 * @property int attestation_id
 * @property string comment
 * @property string status
 * @package App\Model
 */
class HandshiftAnnotation extends AbstractAnnotationModel
{
    protected $with = ['textSelection', 'textSelection.sourceText', 'abbreviation','accentuation','connectivity','correction','curvature','degreeOfFormality','expansion','lineation','orientation','punctuation','regularity','scriptType','slope','wordSplitting'];

    public function getAnnotationType(): string
    {
        return 'handshift';
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function abbreviation(): belongsTo
    {
        return $this->belongsTo(AnnotationAbbreviation::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function accentuation(): belongsTo
    {
        return $this->belongsTo(AnnotationAccentuation::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function connectivity(): belongsTo
    {
        return $this->belongsTo(AnnotationConnectivity::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function correction(): belongsTo
    {
        return $this->belongsTo(AnnotationCorrection::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function curvature(): belongsTo
    {
        return $this->belongsTo(AnnotationCurvature::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function degreeOfFormality(): belongsTo
    {
        return $this->belongsTo(AnnotationDegreeOfFormality::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function expansion(): belongsTo
    {
        return $this->belongsTo(AnnotationExpansion::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function lineation(): belongsTo
    {
        return $this->belongsTo(AnnotationLineation::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function orientation(): belongsTo
    {
        return $this->belongsTo(AnnotationOrientation::class);
    }


    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function punctuation(): belongsTo
    {
        return $this->belongsTo(AnnotationPunctuation::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function regularity(): belongsTo
    {
        return $this->belongsTo(AnnotationRegularity::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function scriptType(): belongsTo
    {
        return $this->belongsTo(AnnotationScriptType::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function slope(): belongsTo
    {
        return $this->belongsTo(AnnotationSlope::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function wordSplitting(): belongsTo
    {
        return $this->belongsTo(AnnotationWordSplitting::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function attestation(): belongsTo
    {
        return $this->belongsTo(Attestation::class);
    }

}
