<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class LanguageAnnotation
 *
 * @property int $language_annotation_id
 * @property int $text_selection_id
 * @property int $annotation_bigraphism_comments_id
 * @property int $annotation_bigraphism_domain_id
 * @property int $annotation_bigraphism_formulaicity_id
 * @property int $annotation_bigraphism_rank_id
 * @property int $annotation_bigraphism_type_id
 * @property int $annotation_codeswitching_comments_id
 * @property int $annotation_codeswitching_domain_id
 * @property int $annotation_codeswitching_formulaicity_id
 * @property int $annotation_codeswitching_rank_id
 * @property int $annotation_codeswitching_type_id
 * @property int $annotation_other_comments_id
 * @property int $annotation_other_domain_id
 * @property int $annotation_other_formulaicity_id
 * @property int $annotation_other_rank_id
 * @property int $annotation_other_type_id
 * @package App\Model
 */
class LanguageAnnotation extends AbstractAnnotationModel
{
    public function getAnnotationType(): string
    {
        return 'language';
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function bigraphismComments(): belongsTo
    {
        return $this->belongsTo(AnnotationBigraphismComments::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function bigraphismDomain(): belongsTo
    {
        return $this->belongsTo(AnnotationBigraphismDomain::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function bigraphismFormulaicity(): belongsTo
    {
        return $this->belongsTo(AnnotationBigraphismFormulaicity::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function bigraphismRank(): belongsTo
    {
        return $this->belongsTo(AnnotationBigraphismRank::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function bigraphismType(): belongsTo
    {
        return $this->belongsTo(AnnotationBigraphismComments::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function codeswitchingComments(): belongsTo
    {
        return $this->belongsTo(AnnotationCodeswitchingComments::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function codeswitchingDomain(): belongsTo
    {
        return $this->belongsTo(AnnotationCodeswitchingDomain::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function codeswitchingRank(): belongsTo
    {
        return $this->belongsTo(AnnotationCodeswitchingRank::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function codeswitchingType(): belongsTo
    {
        return $this->belongsTo(AnnotationCodeswitchingType::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function otherComments(): belongsTo
    {
        return $this->belongsTo(AnnotationOtherComments::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function otherDomain(): belongsTo
    {
        return $this->belongsTo(AnnotationOtherDomain::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function otherFormulaicity(): belongsTo
    {
        return $this->belongsTo(AnnotationOtherFormulaicity::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function otherRank(): belongsTo
    {
        return $this->belongsTo(AnnotationOtherRank::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function otherType(): belongsTo
    {
        return $this->belongsTo(AnnotationOtherType::class);
    }


}
