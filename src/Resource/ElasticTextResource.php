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

            'text_type' => new ElasticIdNameResource($this->textType),
            'text_subtype' => new ElasticIdNameResource($this->textSubtype),

            'collaborator' => ElasticIdNameResource::collection($this->collaborators)->toArray(null),
            'social_distance' => ElasticIdNameResource::collection($this->socialDistances)->toArray(null),
            'project' => ElasticIdNameResource::collection($this->projects)->toArray(null),
            'keyword' => ElasticIdNameResource::collection($this->keywords)->toArray(null),

            'location_found' => ElasticIdNameResource::collection($this->locationsFound)->toArray(null),
            'location_written' => ElasticIdNameResource::collection($this->locationsWritten)->toArray(null),

            'agentive_role' => ElasticAgentiveRoleResource::collection($this->textAgentiveRoles)->toArray(null),
            'communicative_goal' => ElasticCommunicativeGoalResource::collection($this->textCommunicativeGoals)->toArray(null),

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

        // add annotations
        $ret['annotations'] = array_merge(
            BaseElasticAnnotationResource::collection($this->languageAnnotations)->toArray(null),
            BaseElasticAnnotationResource::collection($this->typographyAnnotations)->toArray(null),
            BaseElasticAnnotationResource::collection($this->lexisAnnotations)->toArray(null),
            BaseElasticAnnotationResource::collection($this->orthographyAnnotations)->toArray(null),
            BaseElasticAnnotationResource::collection($this->morphologyAnnotations)->toArray(null),
            BaseElasticAnnotationResource::collection($this->morphoSyntacticalAnnotations)->toArray(null)
        );

        return $ret;
    }
}