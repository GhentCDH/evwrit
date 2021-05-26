<?php

namespace App\Resource;


class TextElasticResource extends BaseResource
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

Name ancient person
TM Person ID
Patronymic

Role
Gender
Occupation
Social rank (main)
Social rank (hypertype)
Honorofic epithet
Domicile
Type domicile

Status Revision

 */

        return [
            'id' => $this->getId(),
            'tm_id' => $this->tm_id,
            'title' => $this->title,
            'text' => $this->text,
            'year_begin' => $this->year_begin,
            'year_end' => $this->year_end,
            'era' => new IdNameResource($this->era),
            'archive' => new IdNameResource($this->archive),

            'material' => IdNameResource::collection($this->materials)->toArray(null),
            'language' => IdNameResource::collection($this->languages)->toArray(null),

            'text_type' => new IdNameResource($this->textType),
            'text_subtype' => new IdNameResource($this->textSubtype),

            'collaborator' => IdNameResource::collection($this->collaborators)->toArray(null),
            'social_distance' => IdNameResource::collection($this->socialDistances)->toArray(null),
            'project' => IdNameResource::collection($this->projects)->toArray(null),
            'keyword' => IdNameResource::collection($this->keywords)->toArray(null),

            'location_found' => IdNameResource::collection($this->locationsFound)->toArray(null),
            'location_written' => IdNameResource::collection($this->locationsWritten)->toArray(null),

            'agentive_role' => AgentiveRoleElasticResource::collection($this->textAgentiveRoles)->toArray(null),
            'communicative_goal' => CommunicativeGoalElasticResource::collection($this->textCommunicativeGoals)->toArray(null),

            'attestation_education' => AttestationEducationElasticResource::collection(
                $this->attestations->reject( fn($attestation) => $attestation->education_id == null )
            )->toArray(null),

            'attestation_age' => AttestationAgeElasticResource::collection(
                $this->attestations->reject( fn($attestation) => $attestation->age_id == null )
            )->toArray(null),

            'attestation_graph_type' => AttestationGraphTypeElasticResource::collection(
                $this->attestations->reject( fn($attestation) => $attestation->graph_type_id == null )
            )->toArray(null),
        ];
    }
}