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

        // text structure
        $ret['generic_text_structure'] = ElasticGenericTextStructureResource::collection($this->genericTextStructure)->toArray();
//        dump($ret['generic_text_structure']);
//        $ret['layout_text_structure'] = ElasticLayoutTextStructure::collection($this->genericTextStructure);
        $ret['text_level'] = BaseResource::collection($this->textLevels)->toArray();

        // annotations
        $ret['annotations'] = array_merge(
            BaseElasticAnnotationResource::collection($this->languageAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($this->typographyAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($this->lexisAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($this->orthographyAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($this->morphologyAnnotations)->toArray(),
            BaseElasticAnnotationResource::collection($this->morphoSyntacticalAnnotations)->toArray(),
            $handshifts = ElasticHandshiftAnnotationResource::collection($this->handshiftAnnotations)->toArray()
        );

        // calculate base annotations / handshift intersects, add handshift properties to annotation properties
        foreach($ret['annotations'] as &$annotation) {
            // collect handshift properties, avoid duplicates by checking id key
            $annotation_handshift = [];
            foreach ($handshifts as $handshift) {
                if ($annotation['type'] != 'handshift' && $this->textSelectionIntersect($handshift['text_selection'], $annotation['text_selection'])) {
                    foreach (array_filter($handshift['properties']) as $h_key => $h_value) {
                        if (!isset($annotation_handshift[$h_key])) {
                            $annotation_handshift[$h_key] = [];
                        }
                        $annotation_handshift[$h_key][$h_value['id']] = $h_value;
                    }
                }
            }
            // remove handshift value keys
            foreach ($annotation_handshift as $h_key => $h_value) {
                $annotation_handshift[$h_key] = array_values($h_value);
            }
            // merge handshift properties with annotation properties
            $annotation['properties'] += $annotation_handshift;
            if (count($annotation_handshift)) {
                $annotation['has_handshift'] = self::boolean(1); // reduce values to 1
            }
        }

        // calculate base annotations / text_structure intersects, add text_structure properties
        foreach($ret['annotations'] as &$annotation) {
            $annotation['text_level'] = [];
            $annotation['generic_text_structure_part'] = [];
            foreach ($ret['generic_text_structure'] ?? [] as $structure ) {
                if ( $this->textSelectionIntersect($structure['text_selection'], $annotation['text_selection']) ) {
                    if ( $text_level = $structure['text_level'] ) {
                        $annotation['text_level'][ $text_level['number'] ] = $text_level; // prevent doubles using string key
                    }
                    if ( $part = $structure['part'] ) {
                        $annotation['generic_text_structure_part'][ $part['id'] ] = $part; // prevent doubles using string key
                    }
                }
            }
            // delete string keys
            $annotation['text_level'] = array_values($annotation['text_level']);
            $annotation['generic_text_structure_part'] = array_values($annotation['generic_text_structure_part']);
        }

        foreach($ret['annotations'] as &$annotation) {
            $annotation['layout_text_structure_part'] = [];
            foreach ( $ret['layout_text_structure'] ?? [] as $structure ) {
                if ( $this->textSelectionIntersect($structure['text_selection'], $annotation['text_selection']) ) {
                    $annotation['layout_text_structure_part'][] = $structure['part'];
//                    dump($annotation);
                }
            }
        }

        return $ret;
    }

    private function textSelectionIntersect($a, $b) {
            $min = $a['selection_start'] < $b['selection_start'] ? $a : $b;
            $max = $min['id'] == $a['id'] ? $b : $a;

            //min ends before max starts -> no intersection
            if ($min['selection_end'] < $max['selection_start']) return false; //the ranges don't intersect

            return [$max['selection_start'], $min['selection_end'] < $max['selection_end'] ? $min['selection_end'] : $max['selection_end']];
    }
}