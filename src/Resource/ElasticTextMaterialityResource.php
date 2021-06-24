<?php

namespace App\Resource;


class ElasticTextMaterialityResource extends ElasticBaseResource
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
/*
 * EVWRIT ID

Patronymic

Domicile
Type domicile

Status Revision
 */

        return [
            /* shared */
            'id' => $this->getId(),
            'tm_id' => $this->tm_id,
            'title' => $this->title,
            'year_begin' => $this->year_begin,
            'year_end' => $this->year_end,
            'era' => new ElasticIdNameResource($this->era),
            'archive' => new ElasticIdNameResource($this->archive),

            'material' => ElasticIdNameResource::collection($this->materials)->toArray(null),
            'language' => ElasticIdNameResource::collection($this->languages)->toArray(null),

            'text_type' => new ElasticIdNameResource($this->textType),
            'text_subtype' => new ElasticIdNameResource($this->textSubtype),

            'collaborator' => ElasticIdNameResource::collection($this->collaborators)->toArray(null),
            'social_distance' => ElasticIdNameResource::collection($this->socialDistances)->toArray(null),
            'project' => ElasticIdNameResource::collection($this->projects)->toArray(null),
            'keyword' => ElasticIdNameResource::collection($this->keywords)->toArray(null),

            'agentive_role' => AgentiveRoleElasticResource::collection($this->textAgentiveRoles)->toArray(null),
            'communicative_goal' => CommunicativeGoalElasticResource::collection($this->textCommunicativeGoals)->toArray(null),

            /* unique */
            'production_stage' =>ElasticIdNameResource::collection($this->productionStages)->toArray(null),
            'writing_direction' => ElasticIdNameResource::collection($this->writingDirections)->toArray(null),
            'text_format' => new ElasticIdNameResource($this->textFormat),

            'is_recto' => self::boolean($this->is_recto),
            'is_verso' => self::boolean($this->is_verso),
            'is_transversa_charta' => self::boolean($this->is_transversa_charta),

            'lines_min' => $this->lines_min,
            'lines_max' => $this->lines_max,

            'columns_min' => $this->columns_min,
            'columns_max' => $this->columns_max,

            'letters_per_line_min' => $this->letters_per_line_min,
            'letters_per_line_max' => $this->letters_per_line_max,
            'interlinear_space' => $this->interlinear_space,

            'margin_left' => $this->margin_left,
            'margin_right' => $this->margin_right,
            'margin_top' => $this->margin_top,
            'margin_bottom' => $this->margin_bottom,

            'width' => $this->width,
            'height' => $this->height
        ];
    }
}