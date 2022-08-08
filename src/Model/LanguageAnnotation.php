<?php

namespace App\Model;

use App\Model\Lookup\AnnotationBigraphismComments;
use App\Model\Lookup\AnnotationBigraphismDomain;
use App\Model\Lookup\AnnotationBigraphismFormulaicity;
use App\Model\Lookup\AnnotationBigraphismRank;
use App\Model\Lookup\AnnotationBigraphismType;
use App\Model\Lookup\AnnotationCodeswitchingComments;
use App\Model\Lookup\AnnotationCodeswitchingDomain;
use App\Model\Lookup\AnnotationCodeswitchingRank;
use App\Model\Lookup\AnnotationCodeswitchingType;
use App\Model\Lookup\AnnotationOtherComments;
use App\Model\Lookup\AnnotationOtherDomain;
use App\Model\Lookup\AnnotationOtherFormulaicity;
use App\Model\Lookup\AnnotationOtherRank;
use App\Model\Lookup\AnnotationOtherType;
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
    protected $with = ['textSelection','textSelection.sourceText','bigraphismComments','bigraphismDomain','bigraphismFormulaicity','bigraphismRank','bigraphismType','codeswitchingComments','codeswitchingDomain','codeswitchingRank','codeswitchingType','otherComments','otherDomain','otherFormulaicity','otherRank','otherType'];

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
        return $this->belongsTo(AnnotationBigraphismType::class);
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
