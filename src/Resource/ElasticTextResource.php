<?php

namespace App\Resource;

use App\Model\Text;

/**
 * Class AttestationResource
 * @package App\Resource
 * @mixin Text
 */
class ElasticTextResource extends ElasticBaseResource
{
    use TraitTextSelectionIntersect;

    const CACHENAME = "text_elastic";

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null)
    {
        /** @var Text $text */
        $text = $this->resource;

        $attributes = $text->attributesToArray();

        $ret = array_merge($attributes, [
            /* properties */
            'id' => $text->getId(),
            'text' => TextSelectionResource::convertNewlines($text->text),
            'text_lemmas' => TextSelectionResource::convertNewlines($text->text_lemmas),
            'apparatus' => TextSelectionResource::convertNewlines($text->apparatus),

            'translation' => TranslationResource::collection($text->translations)->toArray(),

            'era' => new ElasticIdNameResource($text->era),

            'archive' => new ElasticIdNameResource($text->archive),

            'language' => ElasticIdNameResource::collection($text->languages)->toArray(),
            'language_count' => count($text->languages),
            'script' => ElasticIdNameResource::collection($text->scripts)->toArray(),
            'script_count' => count($text->scripts),

            'collaborator' => ElasticIdNameResource::collection($text->collaborators)->toArray(),
            'social_distance' => ElasticIdNameResource::collection($text->socialDistances)->toArray(),
            'project' => ElasticIdNameResource::collection($text->projects)->toArray(),
            'keyword' => ElasticIdNameResource::collection($text->keywords)->toArray(),

            'location_found' => ElasticIdNameResource::collection($text->locationsFound)->toArray(),
            'location_written' => ElasticIdNameResource::collection($text->locationsWritten)->toArray(),

//            'agentive_role' => ElasticAgentiveRoleResource::collection($text->agentiveRoles)->toArray(),
//            'communicative_goal' => ElasticCommunicativeGoalResource::collection($text->communicativeGoals)->toArray(),

            /* links */
            'image' => ImageResource::collection($text->images)->toArray(),
            'link' => LinkResource::collection($text->links)->toArray(),

            /* attestation */
//            'attestations' => ElasticAttestationResource::collection($text->attestations)->toArray(),

            /* materiality */

            'is_recto' => self::boolean($text->is_recto),
            'is_verso' => self::boolean($text->is_verso),
            'is_transversa_charta' => self::boolean($text->is_transversa_charta),

            'lines' => !is_null($text->count_lines) ? [ 'min' => $text->count_lines, 'max' => $text->count_lines ] : ( is_null($text->lines_min) ? null : [ 'min' => $text->lines_min, 'max' => $text->lines_max ] ),
            'columns' => is_null($text->columns_min) ? null : [ 'min' => $text->columns_min, 'max' => $text->columns_max ],
            'letters_per_line' => is_null($text->letters_per_line_min) ? null : [ 'min' => $text->letters_per_line_min, 'max' => $text->letters_per_line_max ],
//            'interlinear_space' => $text->interlinear_space,

            'writing_direction' => ElasticIdNameResource::collection($text->writingDirections)->toArray(),
            'text_format' => new ElasticIdNameResource($text->textFormat),
            'material' => ElasticIdNameResource::collection($text->materials)->toArray(),

            // annotations placeholder
            'annotations' => []
        ]);

        // flatten level properties
        // todo: fix legacy 'ancient_person'
        $textLevels = ElasticTextLevelResource::collection($text->textLevels)->toArray();
        $textLevelProperties = array_merge_recursive(...$textLevels);
        unset($textLevelProperties['number']);
        $textLevelProperties['ancient_person'] = $textLevelProperties['attestations'] ?? null;
        unset($textLevelProperties['attestations']);

        $ret = array_merge($ret, $textLevelProperties);

        // base annotations
        $ret['annotations'] = array_merge(
            BaseElasticAnnotationResource::collection($text->languageAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($text->typographyAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($text->lexisAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($text->orthographyAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($text->morphologyAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($text->morphoSyntacticalAnnotations)->toArray(),
        );

        // handshift annotations
        $handshiftAnnotations = ElasticHandshiftAnnotationResource::collection($text->handshiftAnnotations)->toArray();

        // generic/layout text structure
        $genericTextStructure = ElasticGenericTextStructureResource::collection($text->genericTextStructures)->toArray();
        $ret['has_generic_text_structure'] = self::boolean(count($genericTextStructure) > 0);
        $layoutTextStructure = ElasticLayoutTextStructureResource::collection($text->layoutTextStructures)->toArray();
        $ret['has_layout_text_structure'] = self::boolean(count($layoutTextStructure) > 0);

        // text levels
        $ret['text_level'] = ElasticTextLevelResource::collection($text->textLevels)->toArray();

        // calculate base annotations intersect with text_structure and handshift
        foreach($ret['annotations'] as &$annotationSource) {
            $this->annotationIntersect($annotationSource, $genericTextStructure ?? [], ['gts_part', 'textLevel']);
            $this->annotationIntersect($annotationSource, $layoutTextStructure ?? [], ['gts_part', 'textLevel']);
            $this->annotationIntersect($annotationSource, $handshiftAnnotations);
        }

        // generic/layout text structure annotations
        $gtsAnnotations = ElasticGenericTextStructureAnnotationResource::collection($text->genericTextStructureAnnotations)->toArray();
        $ltsAnnotations = ElasticLayoutTextStructureAnnotationResource::collection($text->layoutTextStructureAnnotations)->toArray();

        // intersect generic text structure annotations with lts, gts, ltsa, handshift
        foreach( $gtsAnnotations as &$annotationSource ) {
            $this->annotationIntersect($annotationSource, $ltsAnnotations);
            $this->annotationIntersect($annotationSource, $handshiftAnnotations);
            $this->annotationIntersect($annotationSource, $genericTextStructure);
            $this->annotationIntersect($annotationSource, $layoutTextStructure);
        }
        // intersect layout text structure annotations with gts, lts, gtsa and handshift
        foreach( $ltsAnnotations as &$annotationSource ) {
            $this->annotationIntersect($annotationSource, $gtsAnnotations);
            $this->annotationIntersect($annotationSource, $handshiftAnnotations);
            $this->annotationIntersect($annotationSource, $genericTextStructure);
            $this->annotationIntersect($annotationSource, $layoutTextStructure);
        }

        // interset generic text structure  with lts and handshift
        foreach( $genericTextStructure as &$annotationSource ) {
            $this->annotationIntersect($annotationSource, $layoutTextStructure);
            $this->annotationIntersect($annotationSource, $handshiftAnnotations);
        }

        // interset layout text structure  with gts and handshift
        foreach( $layoutTextStructure as &$annotationSource ) {
            $this->annotationIntersect($annotationSource, $genericTextStructure);
            $this->annotationIntersect($annotationSource, $handshiftAnnotations);
        }

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