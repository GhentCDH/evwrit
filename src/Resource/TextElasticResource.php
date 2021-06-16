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
            'era' => new IdNameElasticResource($this->era),
            'archive' => new IdNameElasticResource($this->archive),

            'material' => IdNameElasticResource::collection($this->materials)->toArray(null),
            'language' => IdNameElasticResource::collection($this->languages)->toArray(null),

            'text_type' => new IdNameElasticResource($this->textType),
            'text_subtype' => new IdNameElasticResource($this->textSubtype),

            'collaborator' => IdNameElasticResource::collection($this->collaborators)->toArray(null),
            'social_distance' => IdNameElasticResource::collection($this->socialDistances)->toArray(null),
            'project' => IdNameElasticResource::collection($this->projects)->toArray(null),
            'keyword' => IdNameElasticResource::collection($this->keywords)->toArray(null),

            'location_found' => IdNameElasticResource::collection($this->locationsFound)->toArray(null),
            'location_written' => IdNameElasticResource::collection($this->locationsWritten)->toArray(null),

            'agentive_role' => AgentiveRoleElasticResource::collection($this->textAgentiveRoles)->toArray(null),
            'communicative_goal' => CommunicativeGoalElasticResource::collection($this->textCommunicativeGoals)->toArray(null),

            /*
            'attestation_education' => AttestationEducationElasticResource::collection(
                $this->attestations->reject( fn($attestation) => $attestation->education_id == null )
            )->toArray(null),

            'attestation_age' => AttestationAgeElasticResource::collection(
                $this->attestations->reject( fn($attestation) => $attestation->age_id == null )
            )->toArray(null),

            'attestation_graph_type' => AttestationGraphTypeElasticResource::collection(
                $this->attestations->reject( fn($attestation) => $attestation->graph_type_id == null )
            )->toArray(null),
            */

            'ancient_person' => AttestationElasticResource::collection($this->attestations)->toArray(0)
            /*
            'attestation_role' => '',
            'attestation_social_rank' => '',
            'attestation_occupation' => '',
            'attestation_honorific_epithet' => '',

            'ancient_person' => '',

            'ancient_person'
            */

        ];
    }
}