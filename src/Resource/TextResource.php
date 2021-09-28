<?php

namespace App\Resource;

use App\Model\Text;

/**
 * Class TextResource
 * @package App\Resource
 * @property Text $resource
 * @mixin Text
 */
class TextResource extends BaseResource
{
    const CACHENAME = 'text';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        $text = $this->resource;

        return [
            'id' => $text->getId(),
            'tm_id' => $text->tm_id,
            'title' => $text->title,
            'text' => $this->convertNewlines($text->text),
            'apparatus' => $this->convertNewlines($text->apparatus),
            'translation' => TranslationResource::collection($text->translations)->toArray(null),

            'year_begin' => $text->year_begin,
            'year_end' => $text->year_end,

            'era' => new IdNameResource($text->era),
            'archive' => new IdNameResource($text->archive),

            'language' => IdNameResource::collection($text->languages)->toArray(null),

            'text_type' => new IdNameResource($text->textType),
            'text_subtype' => new IdNameResource($text->textSubtype),

            'collaborator' => IdNameResource::collection($text->collaborators)->toArray(null),
            'social_distance' => IdNameResource::collection($text->socialDistances)->toArray(null),
            'project' => IdNameResource::collection($text->projects)->toArray(null),
            'keyword' => IdNameResource::collection($text->keywords)->toArray(null),

            'location_found' => IdNameResource::collection($text->locationsFound)->toArray(null),
            'location_written' => IdNameResource::collection($text->locationsWritten)->toArray(null),

            'agentive_role' => ElasticAgentiveRoleResource::collection($text->textAgentiveRoles)->toArray(null),
            'communicative_goal' => ElasticCommunicativeGoalResource::collection($text->textCommunicativeGoals)->toArray(null),

            'image' => ImageResource::collection($text->images)->toArray(null),
            'link' => LinkResource::collection($text->links)->toArray(null),

            /* materiality */
            'width' => $text->width,
            'height' => $text->height,

            'margin_top' => $text->margin_top,
            'margin_left' => $text->margin_left,
            'margin_right' => $text->margin_right,
            'margin_bottom' => $text->margin_bottom,

            'production_stage' => IdNameResource::collection($text->productionStages)->toArray(null),
            'writing_direction' => IdNameResource::collection($text->writingDirections)->toArray(null),
            'material' => IdNameResource::collection($text->materials)->toArray(null),
            'text_format' => new IdNameResource($text->textFormat),

            'is_recto' => $text->is_recto,
            'is_verso' => $text->is_verso,
            'is_transversa_charta' => $text->is_transversa_charta,
            'kollesis' => $text->kollesis,

            'lines' => is_null($text->lines_min) ? null : [ $text->lines_min, $text->lines_max ],
            'columns' => is_null($text->columns_min) ? null : [ $text->columns_min, $text->columns_max ],
            'letters_per_line_min' => is_null($text->letters_per_line_min) ? null : [ $text->letters_per_line_min, $text->letters_per_line_max ],
            'interlinear_space' => $text->interlinear_space,

            /* attestation */
            'ancient_person' => AttestationResource::collection($text->attestations)->toArray(0)
        ];
    }
}