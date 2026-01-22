<?php

namespace App\Model;

use App\Model\Lookup\AnnotationAspectContent;
use App\Model\Lookup\AnnotationAspectContext;
use App\Model\Lookup\AnnotationAspectForm;
use App\Model\Lookup\AnnotationCaseContent;
use App\Model\Lookup\AnnotationCaseContext;
use App\Model\Lookup\AnnotationCaseForm;
use App\Model\Lookup\AnnotationCliticContent;
use App\Model\Lookup\AnnotationCliticContext;
use App\Model\Lookup\AnnotationCliticForm;
use App\Model\Lookup\AnnotationCoherenceContent;
use App\Model\Lookup\AnnotationCoherenceContext;
use App\Model\Lookup\AnnotationCoherenceForm;
use App\Model\Lookup\AnnotationComplementationContent;
use App\Model\Lookup\AnnotationComplementationContext;
use App\Model\Lookup\AnnotationComplementationForm;
use App\Model\Lookup\AnnotationModalityContent;
use App\Model\Lookup\AnnotationModalityContext;
use App\Model\Lookup\AnnotationModalityForm;
use App\Model\Lookup\AnnotationRelativisationContent;
use App\Model\Lookup\AnnotationRelativisationContext;
use App\Model\Lookup\AnnotationRelativisationForm;
use App\Model\Lookup\AnnotationSubordinationContent;
use App\Model\Lookup\AnnotationSubordinationContext;
use App\Model\Lookup\AnnotationSubordinationForm;
use App\Model\Lookup\AnnotationTypeFormulaicity;
use App\Model\Lookup\AnnotationTypeReconstruction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class TypographyAnnotation
 *
 * @property int $typography_annotation_id
 * @property int $text_selection_id

 * @property string $text
 * @package App\Model
 */
class MorphoSyntacticalAnnotation extends AbstractAnnotationModel
{
    protected $with = [
        'textSelection',
        'complementationContent','complementationContext','complementationForm',
        'coherenceContent','coherenceContext','coherenceForm',
        'subordinationContent','subordinationForm','subordinationContext',
        'relativisationContent','relativisationContext','relativisationForm',
        'typeFormulaicity', 'typeReconstruction',
//        'aspectContent','aspectContext','aspectForm',
//        'modalityContent','modalityContext','modalityForm',
//        'cliticContent','cliticContext','cliticForm',
//        'caseContent','caseContext','caseForm',
        'override'
    ];

    public function getAnnotationType(): string
    {
        return 'morpho_syntactical';
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function aspectContent(): belongsTo
    {
        return $this->belongsTo(AnnotationAspectContent::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function aspectContext(): belongsTo
    {
        return $this->belongsTo(AnnotationAspectContext::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function aspectForm(): belongsTo
    {
        return $this->belongsTo(AnnotationAspectForm::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function complementationContent(): belongsTo
    {
        return $this->belongsTo(AnnotationComplementationContent::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function complementationContext(): belongsTo
    {
        return $this->belongsTo(AnnotationComplementationContext::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function complementationForm(): belongsTo
    {
        return $this->belongsTo(AnnotationComplementationForm::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function modalityContent(): belongsTo
    {
        return $this->belongsTo(AnnotationModalityContent::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function modalityContext(): belongsTo
    {
        return $this->belongsTo(AnnotationModalityContext::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function modalityForm(): belongsTo
    {
        return $this->belongsTo(AnnotationModalityForm::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function coherenceContent(): belongsTo
    {
        return $this->belongsTo(AnnotationCoherenceContent::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function coherenceContext(): belongsTo
    {
        return $this->belongsTo(AnnotationCoherenceContext::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function coherenceForm(): belongsTo
    {
        return $this->belongsTo(AnnotationCoherenceForm::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function cliticContent(): belongsTo
    {
        return $this->belongsTo(AnnotationCliticContent::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function cliticContext(): belongsTo
    {
        return $this->belongsTo(AnnotationCliticContext::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function cliticForm(): belongsTo
    {
        return $this->belongsTo(AnnotationCliticForm::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function caseContent(): belongsTo
    {
        return $this->belongsTo(AnnotationCaseContent::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function caseContext(): belongsTo
    {
        return $this->belongsTo(AnnotationCaseContext::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function caseForm(): belongsTo
    {
        return $this->belongsTo(AnnotationCaseForm::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function subordinationContent(): belongsTo
    {
        return $this->belongsTo(AnnotationSubordinationContent::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function subordinationForm(): belongsTo
    {
        return $this->belongsTo(AnnotationSubordinationForm::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function subordinationContext(): belongsTo
    {
        return $this->belongsTo(AnnotationSubordinationContext::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function relativisationContent(): belongsTo
    {
        return $this->belongsTo(AnnotationRelativisationContent::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function relativisationContext(): belongsTo
    {
        return $this->belongsTo(AnnotationRelativisationContext::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function relativisationForm(): belongsTo
    {
        return $this->belongsTo(AnnotationRelativisationForm::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function typeFormulaicity(): belongsTo
    {
        return $this->belongsTo(AnnotationTypeFormulaicity::class);
    }

    /**
     * @return BelongsTo|IdNameModel
     * @throws ReflectionException
     */
    public function typeReconstruction(): belongsTo
    {
        return $this->belongsTo(AnnotationTypeReconstruction::class);
    }
}
