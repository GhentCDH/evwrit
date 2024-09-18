<?php

namespace App\Resource;

use App\Model\Text;

/**
 * Class AttestationResource
 * @package App\Resource
 * @mixin Text
 */
class ElasticTextAnnotationsResource extends ElasticBaseResource
{
    use TraitTextSelectionIntersect;

    const CACHENAME = "text_elastic";

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        /** @var Text $text */
        $text = $this->resource;

        $ret = [
            /* properties */
            'id' => $text->getId(),
            'text_id' => $text->text,

            // annotations placeholder
            'annotations' => []
        ];

        // base annotations
        $ret['annotations'] = array_merge(
            BaseElasticAnnotationResource::collection(
                $text->languageAnnotations->map( fn($a) => new BaseElasticAnnotationResource($a, $text) )
            )->toArray(),
            BaseElasticAnnotationResource::collection(
                $text->typographyAnnotations->map( fn($a) => new BaseElasticAnnotationResource($a, $text) )
            )->toArray(),
            BaseElasticAnnotationResource::collection(
                $text->lexisAnnotations->map( fn($a) => new BaseElasticAnnotationResource($a, $text) )
            )->toArray(),
            BaseElasticAnnotationResource::collection(
                $text->orthographyAnnotations->map( fn($a) => new BaseElasticAnnotationResource($a, $text) )
            )->toArray(),
            BaseElasticAnnotationResource::collection(
                $text->morphologyAnnotations->map( fn($a) => new BaseElasticAnnotationResource($a, $text) )
            )->toArray(),
            BaseElasticAnnotationResource::collection(
                $text->morphoSyntacticalAnnotations->map( fn($a) => new BaseElasticAnnotationResource($a, $text) )
            )->toArray(),
        );

        // handshift annotations
        $handshiftAnnotations = ElasticHandshiftAnnotationResource::collection(
            $text->handshiftAnnotations->map( fn($a) => new ElasticHandshiftAnnotationResource($a, $text) )
        )->toArray();

        // generic/layout text structure
        $genericTextStructure = ElasticGenericTextStructureResource::collection(
            $text->genericTextStructures
        )->toArray();
        $ret['has_generic_text_structure'] = self::boolean(count($genericTextStructure) > 0);

        $layoutTextStructure = ElasticLayoutTextStructureResource::collection(
            $text->layoutTextStructures
        )->toArray();
        $ret['has_layout_text_structure'] = self::boolean(count($layoutTextStructure) > 0);

        // text levels
//        $ret['text_level'] = ElasticTextLevelResource::collection($text->textLevels)->toArray();

        // generic/layout text structure annotations
        $gtsAnnotations = ElasticGenericTextStructureAnnotationResource::collection(
            $text->genericTextStructureAnnotations->map( fn($a) => new ElasticGenericTextStructureAnnotationResource($a, $text) )
        )->toArray();
        $ltsAnnotations = ElasticLayoutTextStructureAnnotationResource::collection(
            $text->layoutTextStructureAnnotations->map( fn($a) => new ElasticLayoutTextStructureAnnotationResource($a, $text) )
        )->toArray();

        $ret['annotations'] = array_merge(
            $ret['annotations'],
            $gtsAnnotations,
            $ltsAnnotations,
            $genericTextStructure,
            $layoutTextStructure,
            $handshiftAnnotations
        );

        return $ret;
    }
}