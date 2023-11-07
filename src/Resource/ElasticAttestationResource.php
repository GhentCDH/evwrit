<?php

namespace App\Resource;


use App\Model\AncientPerson;
use App\Model\Attestation;

/**
 * Class ElasticAttestationResource
 * @package App\Resource
 * @mixin Attestation
 */
class ElasticAttestationResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        /** @var Attestation $attestation */
        $attestation = $this->resource;

        $person = $attestation->ancientPerson;

        return [
            /* ancient person properties */
            'id' => $person->getId(),
            'id_name' => $person->getId()."_".$person->getName(),

            'tm_id' => $person->getTmId(),
            'name' => $person->getName(),
            'gender' => new ElasticIdNameResource($person->gender),
            'patronimic' => $person->patronymic,

            /* attestation properties */
            'education' => new ElasticIdNameResource($this->education),
            'age' => new ElasticIdNameResource($this->age),
            'graph_type' => new ElasticIdNameResource($this->graphType),
            'role' => ElasticIdNameResource::collection($this->roles)->toArray(),
            'social_rank' => ElasticIdNameResource::collection($this->socialRanks)->toArray(),
            'occupation' => ElasticOccupationResource::collection($this->occupations)->toArray(),
            'honorific_epithet' => ElasticIdNameResource::collection($this->honorificEpithets)->toArray(),
        ];
    }
}