<?php

namespace App\Resource;

use App\Model\Text;

/**
 * Class AttestationResource
 * @package App\Resource
 * @mixin Text
 */
class TextResource extends BaseResource
{
    const CACHENAME = "text";

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        return [
            'id' => $this->getId(),
            'tm_id' => $this->tm_id,
            'title' => $this->title,
            'text' => $this->convertNewlines($this->text),
            'apparatus' => $this->convertNewlines($this->text),
            'translation' => TranslationResource::collection($this->translations)->toArray(null),

            'year_begin' => $this->year_begin,
            'year_end' => $this->year_end,

            'era' => new IdNameResource($this->era),
            'archive' => new IdNameResource($this->archive),

            'language' => IdNameResource::collection($this->languages)->toArray(null),

            'text_type' => new IdNameResource($this->textType),
            'text_subtype' => new IdNameResource($this->textSubtype),

            'collaborator' => IdNameResource::collection($this->collaborators)->toArray(null),
            'social_distance' => IdNameResource::collection($this->socialDistances)->toArray(null),
            'project' => IdNameResource::collection($this->projects)->toArray(null),
            'keyword' => IdNameResource::collection($this->keywords)->toArray(null),

            'location_found' => IdNameResource::collection($this->locationsFound)->toArray(null),
            'location_written' => IdNameResource::collection($this->locationsWritten)->toArray(null),

            'agentive_role' => ElasticAgentiveRoleResource::collection($this->textAgentiveRoles)->toArray(null),
            'communicative_goal' => ElasticCommunicativeGoalResource::collection($this->textCommunicativeGoals)->toArray(null),

            'image' => ImageResource::collection($this->images)->toArray(null),
            'link' => LinkResource::collection($this->links)->toArray(null),

            /* materiality */
            'width' => $this->width,
            'height' => $this->height,

            'margin_top' => $this->margin_top,
            'margin_left' => $this->margin_left,
            'margin_right' => $this->margin_right,
            'margin_bottom' => $this->margin_bottom,

            'production_stage' => IdNameResource::collection($this->productionStages)->toArray(null),
            'writing_direction' => IdNameResource::collection($this->writingDirections)->toArray(null),
            'material' => IdNameResource::collection($this->materials)->toArray(null),
            'text_format' => new IdNameResource($this->textFormat),

            'is_recto' => $this->is_recto,
            'is_verso' => $this->is_verso,
            'is_transversa_charta' => $this->is_transversa_charta,
            'kollesis' => $this->kollesis,

            'lines' => is_null($this->lines_min) ? null : [ $this->lines_min, $this->lines_max ],
            'columns' => is_null($this->columns_min) ? null : [ $this->columns_min, $this->columns_max ],
            'letters_per_line_min' => is_null($this->letters_per_line_min) ? null : [ $this->letters_per_line_min, $this->letters_per_line_max ],
            'interlinear_space' => $this->interlinear_space,

            /* attestation */
            'ancient_person' => AttestationResource::collection($this->attestations)->toArray(0)
        ];
    }
}