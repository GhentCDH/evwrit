<?php

namespace App\Resource;


use App\Model\AncientPerson;
use App\Model\Attestation;

/**
 * Class AttestationElasticResource
 * @package App\Resource
 * @mixin Attestation
 */
class AttestationElasticResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null)
    {
        /** @var AncientPerson $ap */
        $ap = $this->ancientPerson;

        return [
            'id' => $this->ancient_person_id,
            'id_name' => $this->ancient_person_id."_".$ap->getName(),


            'tm_id' => $ap->getTmId(),
            'name' => $ap->getName(),
            'gender' => new IdNameElasticResource($ap->gender),

            'education' => new IdNameElasticResource($this->education),
            'age' => new IdNameElasticResource($this->age),
            'graph_type' => new IdNameElasticResource($this->graphType),
            'role' => IdNameElasticResource::collection($this->roles)->toArray(0),
            'social_rank' => IdNameElasticResource::collection($this->socialRanks)->toArray(0),
            'occupation' => IdNameElasticResource::collection($this->occupations)->toArray(0),
            'honorific_epithet' => IdNameElasticResource::collection($this->honorificEpithets)->toArray(0),
        ];
    }
}