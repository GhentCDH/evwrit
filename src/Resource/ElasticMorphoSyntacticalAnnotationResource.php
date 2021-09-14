<?php

namespace App\Resource;

use App\Model\MorphoSyntacticalAnnotation;
use function Symfony\Component\String\u;


/**
 * Class ElasticMorphoSyntacticalAnnotationResource
 * @package App\Resource
 * @mixin MorphoSyntacticalAnnotation
 */
class ElasticMorphoSyntacticalAnnotationResource extends BaseAnnotationResource
{
    protected const annotationLookupModels = [
        'AnnotationAspectContent',
        'AnnotationAspectContext',
        'AnnotationAspectForm',
        'AnnotationComplementationContent',
        'AnnotationComplementationContext',
        'AnnotationComplementationForm',
        'AnnotationModalityContent',
        'AnnotationModalityContext',
        'AnnotationModalityForm',
        'AnnotationCoherenceContent',
        'AnnotationCoherenceContext',
        'AnnotationCoherenceForm',
        'AnnotationCliticContent',
        'AnnotationCliticContext',
        'AnnotationCliticForm',
        'AnnotationCaseContent',
        'AnnotationCaseContext',
        'AnnotationCaseForm',
        'AnnotationSubordinationContent',
        'AnnotationSubordinationForm',
        'AnnotationSubordinationContext',
        'AnnotationOrderContent',
        'AnnotationOrderContext',
        'AnnotationOrderForm',
        'AnnotationTypeFormulaicity',
        'AnnotationTypeReconstruction',
    ];
}
