<?php

namespace App\Resource;

use App\Model\LanguageAnnotation;
use function Symfony\Component\String\u;


/**
 * Class ElasticLanguageAnnotationResource
 * @package App\Resource
 * @mixin LanguageAnnotation
 */
class ElasticLanguageAnnotationResource extends BaseAnnotationResource
{
    protected const annotationLookupModels = [
        'AnnotationBigraphismComments',
        'AnnotationBigraphismDomain',
        'AnnotationBigraphismFormulaicity',
        'AnnotationBigraphismRank',
        'AnnotationBigraphismType',
        'AnnotationCodeswitchingComments',
        'AnnotationCodeswitchingDomain',
        'AnnotationCodeswitchingFormulaicity',
        'AnnotationCodeswitchingRank',
        'AnnotationCodeswitchingType',
        'AnnotationOtherComments',
        'AnnotationOtherDomain',
        'AnnotationOtherFormulaicity',
        'AnnotationOtherRank',
        'AnnotationOtherType',
    ];

}