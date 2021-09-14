<?php

namespace App\Resource;

use App\Model\TypographyAnnotation;


/**
 * Class ElasticTypographyAnnotationResource
 * @package App\Resource
 * @mixin TypographyAnnotation
 */
class ElasticTypographyAnnotationResource extends BaseAnnotationResource
{
    protected const annotationLookupModels = [
        'AnnotationAbbreviation',
        'AnnotationAccentuation',
        'AnnotationAccronym',
        'AnnotationInsertion',
        'AnnotationExpansion',
        'AnnotationConnectivity',
        'AnnotationCorrection',
        'AnnotationCurvature',
        'AnnotationDeletion',
        'AnnotationOrientation',
        'AnnotationVacat',
        'AnnotationWeight',
        'AnnotationSymbol',
        'AnnotationWordSplitting',
        'AnnotationWordClass',
        'AnnotationPunctuation',
        'AnnotationPositionInText',
        'AnnotationRegularity',
        'AnnotationSlope',
        'AnnotationScriptType',
    ];
}

