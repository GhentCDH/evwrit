<?php

namespace App\Model;

use App\Model\Lookup\AnnotationAbbreviation;
use App\Model\Lookup\AnnotationAccentuation;
use App\Model\Lookup\AnnotationAccronym;
use App\Model\Lookup\AnnotationConnectivity;
use App\Model\Lookup\AnnotationCorrection;
use App\Model\Lookup\AnnotationCurvature;
use App\Model\Lookup\AnnotationDeletion;
use App\Model\Lookup\AnnotationExpansion;
use App\Model\Lookup\AnnotationInsertion;
use App\Model\Lookup\AnnotationOrientation;
use App\Model\Lookup\AnnotationPositionInText;
use App\Model\Lookup\AnnotationPunctuation;
use App\Model\Lookup\AnnotationRegularity;
use App\Model\Lookup\AnnotationScriptType;
use App\Model\Lookup\AnnotationSlope;
use App\Model\Lookup\AnnotationSymbol;
use App\Model\Lookup\AnnotationVacat;
use App\Model\Lookup\AnnotationWeight;
use App\Model\Lookup\AnnotationWordClass;
use App\Model\Lookup\AnnotationWordSplitting;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class TypographyAnnotation
 *
 * @property int $typography_annotation_id
 * @property int $text_selection_id
 * @property int $annotation_abbreviation_id
 * @property int $annotation_accentuation_id
 * @property int $annotation_accronym_id
 * @property int $annotation_insertion_id
 * @property int $annotation_expansion_id
 * @property int $annotation_connectivity_id
 * @property int $annotation_correction_id
 * @property int $annotation_curvature_id
 * @property int $annotation_deletion_id
 * @property int $annotation_orientation_id
 * @property int $annotation_vacat_id
 * @property int $annotation_weight_id
 * @property int $annotation_symbol_id
 * @property int $annotation_word_splitting_id
 * @property int $annotation_word_class_id
 * @property int $annotation_punctuation_id
 * @property int $annotation_position_in_text_id
 * @property int $annotation_regularity_id
 * @property int $annotation_slope_id
 * @property int $annotation_script_type_id
 * @property string $text
 * @package App\Model
 */
class TypographyAnnotation extends AbstractAnnotationModel
{
    protected $with = ['textSelection',
//        'textSelection.sourceText',
        'abbreviation','accentuation','accronym','insertion','expansion','connectivity','correction','curvature','deletion','orientation','vacat','weight','symbol','wordSplitting','wordClass','punctuation','positionInText','regularity','slope','scriptType',
        'override'
    ];

    public function getAnnotationType(): string
    {
        return 'typography';
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
    public function accronym(): belongsTo
    {
        return $this->belongsTo(AnnotationAccronym::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function insertion(): belongsTo
    {
        return $this->belongsTo(AnnotationInsertion::class);
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
    public function deletion(): belongsTo
    {
        return $this->belongsTo(AnnotationDeletion::class);
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
    public function vacat(): belongsTo
    {
        return $this->belongsTo(AnnotationVacat::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function weight(): belongsTo
    {
        return $this->belongsTo(AnnotationWeight::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function symbol(): belongsTo
    {
        return $this->belongsTo(AnnotationSymbol::class);
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
    public function wordClass(): belongsTo
    {
        return $this->belongsTo(AnnotationWordClass::class);
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
    public function positionInText(): belongsTo
    {
        return $this->belongsTo(AnnotationPositionInText::class);
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
    public function slope(): belongsTo
    {
        return $this->belongsTo(AnnotationSlope::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function scriptType(): belongsTo
    {
        return $this->belongsTo(AnnotationScriptType::class);
    }

}
