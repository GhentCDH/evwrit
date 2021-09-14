<?php

namespace App\Resource;

use App\Model\OrthographyLexisAnnotation;


/**
 * Class ElasticOrthographyLexisAnnotationResource
 * @package App\Resource
 * @mixin OrthographyLexisAnnotation
 */
class ElasticOrthographyLexisAnnotationResource extends BaseAnnotationResource
{
    protected const annotationLookupModels = [
        'AnnotationStandardFormLexis',
        'AnnotationStandardFormMorphology',
        'AnnotationStandardFormOrthography',
        'AnnotationTypeLexis',
        'AnnotationTypeMorphology',
        'AnnotationTypeOrthography',
        'AnnotationSubtypeLexis',
        'AnnotationSubtypeMorphology',
        'AnnotationSubypeOrthography',
        'AnnotationWordclassLexis',
        'AnnotationWordclassMorphology',
        'AnnotationWordclassOrthography',
        'AnnotationFormulaicityLexis',
        'AnnotationFormulaicityMorphology',
        'AnnotationFormulaicityOrthography',
        'AnnotationPrescriptionLexis',
        'AnnotationPrescriptionMorphology',
        'AnnotationPrescriptionOrthography',
        'AnnotationProscriptionLexis',
        'AnnotationProscriptionMorphology',
        'AnnotationProscriptionOrthography',
        'AnnotationPositionInWordLexis',
        'AnnotationPositionInWordMorphology',
        'AnnotationPositionInWordOrthography',
        'AnnotationIdentifierLexis',
        'AnnotationIdentifierMorphology',
        'AnnotationIdentifierOrthography',
    ];
}

