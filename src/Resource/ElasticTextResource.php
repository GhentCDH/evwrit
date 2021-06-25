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

            'agentive_role' => ElasticAgentiveRoleResource::collection($this->textAgentiveRoles)->toArray(null),
            'communicative_goal' => ElasticCommunicativeGoalResource::collection($this->textCommunicativeGoals)->toArray(null),

            /* unique */
            'location_found' => ElasticIdNameResource::collection($this->locationsFound)->toArray(null),
            'location_written' => ElasticIdNameResource::collection($this->locationsWritten)->toArray(null),

            'ancient_person' => ElasticAttestationResource::collection($this->attestations)->toArray(0)
        ];
    }
}