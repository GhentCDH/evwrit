<?php

namespace App\Resource;


use App\Model\AncientPerson;
use App\Model\Attestation;

/**
 * Class AttestationResource
 * @package App\Resource
 * @mixin Attestation
 */
class AttestationResource extends BaseResource
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
            'gender' => new IdNameResource($ap->gender),

            'education' => new IdNameResource($this->education),
            'age' => new IdNameResource($this->age),
            'graph_type' => new IdNameResource($this->graphType),
            'role' => IdNameResource::collection($this->roles)->toArray(0),
            'social_rank' => IdNameResource::collection($this->socialRanks)->toArray(0),
            'occupation' => IdNameResource::collection($this->occupations)->toArray(0),
            'honorific_epithet' => IdNameResource::collection($this->honorificEpithets)->toArray(0),
        ];
    }
}