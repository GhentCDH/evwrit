<?php

namespace App\Model;

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
    public function AnnotationInsertion(): belongsTo
    {
        return $this->belongsTo(AnnotationInsertion::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationExpansion(): belongsTo
    {
        return $this->belongsTo(AnnotationExpansion::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationConnectivity(): belongsTo
    {
        return $this->belongsTo(AnnotationConnectivity::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationCorrection(): belongsTo
    {
        return $this->belongsTo(AnnotationCorrection::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationCurvature(): belongsTo
    {
        return $this->belongsTo(AnnotationCurvature::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationDeletion(): belongsTo
    {
        return $this->belongsTo(AnnotationDeletion::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationOrientation(): belongsTo
    {
        return $this->belongsTo(AnnotationOrientation::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationVacat(): belongsTo
    {
        return $this->belongsTo(AnnotationVacat::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationWeight(): belongsTo
    {
        return $this->belongsTo(AnnotationWeight::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationSymbol(): belongsTo
    {
        return $this->belongsTo(AnnotationSymbol::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationWordSplitting(): belongsTo
    {
        return $this->belongsTo(AnnotationWordSplitting::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationWordClass(): belongsTo
    {
        return $this->belongsTo(AnnotationWordClass::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationPunctuation(): belongsTo
    {
        return $this->belongsTo(AnnotationPunctuation::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationPositionInText(): belongsTo
    {
        return $this->belongsTo(AnnotationPositionInText::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationRegularity(): belongsTo
    {
        return $this->belongsTo(AnnotationRegularity::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationSlope(): belongsTo
    {
        return $this->belongsTo(AnnotationSlope::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function AnnotationScriptType(): belongsTo
    {
        return $this->belongsTo(AnnotationScriptType::class);
    }

}
