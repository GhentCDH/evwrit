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

        $ret = [
            /* properties */
            'id' => $this->getId(),
            'tm_id' => $this->tm_id,
            'title' => $this->title,
            'text' => $this->convertNewlines($text->text),
            'text_lemmas' => $this->convertNewlines($text->text_lemmas),
            'apparatus' => $this->convertNewlines($text->apparatus),

            'translation' => TranslationResource::collection($text->translations)->toArray(null),

            'year_begin' => $this->year_begin,
            'year_end' => $this->year_end,

            'era' => new ElasticIdNameResource($this->era),

            'archive' => new ElasticIdNameResource($this->archive),

            'language' => ElasticIdNameResource::collection($this->languages)->toArray(null),
            'script' => ElasticIdNameResource::collection($this->scripts)->toArray(null),

            'text_type' => new ElasticIdNameResource($this->textType),
            'text_subtype' => new ElasticIdNameResource($this->textSubtype),

            'collaborator' => ElasticIdNameResource::collection($this->collaborators)->toArray(null),
            'social_distance' => ElasticIdNameResource::collection($this->socialDistances)->toArray(null),
            'project' => ElasticIdNameResource::collection($this->projects)->toArray(null),
            'keyword' => ElasticIdNameResource::collection($this->keywords)->toArray(null),

            'location_found' => ElasticIdNameResource::collection($this->locationsFound)->toArray(null),
            'location_written' => ElasticIdNameResource::collection($this->locationsWritten)->toArray(null),

            'agentive_role' => ElasticAgentiveRoleResource::collection($this->agentiveRoles)->toArray(null),
            'communicative_goal' => ElasticCommunicativeGoalResource::collection($this->communicativeGoals)->toArray(null),

            /* links */
            'image' => ImageResource::collection($text->images)->toArray(null),
            'link' => LinkResource::collection($text->links)->toArray(null),

            /* attestation */
            'ancient_person' => ElasticAttestationResource::collection($this->attestations)->toArray(null),

            /* materiality */
            'width' => $this->width,
            'height' => $this->height,

            'margin_left' => $this->margin_left,
            'margin_right' => $this->margin_right,
            'margin_top' => $this->margin_top,
            'margin_bottom' => $this->margin_bottom,

            'is_recto' => self::boolean($this->is_recto),
            'is_verso' => self::boolean($this->is_verso),
            'is_transversa_charta' => self::boolean($this->is_transversa_charta),
            'kollesis' => $text->kollesis,

            'lines' => is_null($text->lines_min) ? null : [ 'min' => $text->lines_min, 'max' => $text->lines_max ],
            'columns' => is_null($text->columns_min) ? null : [ 'min' => $text->columns_min, 'max' => $text->columns_max ],
            'letters_per_line' => is_null($text->letters_per_line_min) ? null : [ 'min' => $text->letters_per_line_min, 'max' => $text->letters_per_line_max ],
            'interlinear_space' => $text->interlinear_space,

            'production_stage' =>ElasticIdNameResource::collection($this->productionStages)->toArray(null),
            'writing_direction' => ElasticIdNameResource::collection($this->writingDirections)->toArray(null),
            'text_format' => new ElasticIdNameResource($this->textFormat),
            'material' => ElasticIdNameResource::collection($this->materials)->toArray(null),

            // annotations placeholder
            'annotations' => []
        ];



        // base annotations
        $ret['annotations'] = array_merge(
            BaseElasticAnnotationResource::collection($this->languageAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($this->typographyAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($this->lexisAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($this->orthographyAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($this->morphologyAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($this->morphoSyntacticalAnnotations)->toArray(),
        );

        // handshift annotations
        $handshiftAnnotations = ElasticHandshiftAnnotationResource::collection($this->handshiftAnnotations)->toArray();

        // generic/layout text structure
        $genericTextStructure = ElasticGenericTextStructureResource::collection($this->genericTextStructure)->toArray();
        $ret['has_generic_text_structure'] = self::boolean(count($genericTextStructure) > 0);
        $layoutTextStructure = ElasticLayoutTextStructureResource::collection($this->layoutTextStructure)->toArray();
        $ret['has_layout_text_structure'] = self::boolean(count($layoutTextStructure) > 0);

        // text levels
        $ret['text_level'] = BaseResource::collection($this->textLevels)->toArray();

        // calculate base annotations intersect with text_structure and handshift
        foreach($ret['annotations'] as &$annotationSource) {
            $this->annotationIntersect($annotationSource, $genericTextStructure ?? [], ['gts_part', 'gts_textLevel']);
            $this->annotationIntersect($annotationSource, $layoutTextStructure ?? [], ['gts_part', 'gts_textLevel']);
            $this->annotationIntersect($annotationSource, $handshiftAnnotations);
        }

        // generic/layout text structure annotations
        $gtsAnnotations = ElasticGenericTextStructureAnnotationResource::collection($this->genericTextStructureAnnotations)->toArray();
        $ltsAnnotations = ElasticLayoutTextStructureAnnotationResource::collection($this->layoutTextStructureAnnotations)->toArray();

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

    private function annotationIntersect(&$annotationSource, $annotations, $limitProperties= []) {
        $additionalProperties = [];
        foreach( $annotations as $annotationTest ) {
            $type = $annotationTest['type'];
            if ($this->textSelectionIntersect($annotationSource['text_selection'], $annotationTest['text_selection'])) {
                $properties = array_filter($annotationTest['properties'], fn($v,$k) => $v && strpos( $k , $type ) === 0, ARRAY_FILTER_USE_BOTH);
                foreach ($properties as $propertyKey => $propertyValue) {
                    if ( $propertyValue['id'] ?? $propertyValue['number'] ?? null) { // todo: dirty!
                        $additionalProperties[$propertyKey][$propertyValue['id'] ?? $propertyValue['number']] = $propertyValue;
                    }
                }
            }
        }
        // remove keys of additional properties
        foreach ($additionalProperties as $propertyKey => $propertyValues) {
            $additionalProperties[$propertyKey] = array_values($propertyValues);
        }
        // merge additional properties with existing properties
        $annotationSource['properties'] += $additionalProperties;
    }

    private function textSelectionIntersect($a, $b) {
            $min = $a['selection_start'] < $b['selection_start'] ? $a : $b;
            $max = $min['id'] == $a['id'] ? $b : $a;

            //min ends before max starts -> no intersection
            if ($min['selection_end'] < $max['selection_start']) return false; //the ranges don't intersect

            return [$max['selection_start'], $min['selection_end'] < $max['selection_end'] ? $min['selection_end'] : $max['selection_end']];
    }
}