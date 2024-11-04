<?php

namespace App\Resource;


use App\Model\AncientPerson;
use App\Model\Attestation;

/**
 * Class AttestationResource
 * @package App\Resource
 * @mixin Attestation
 */
class AttestationAncientPersonOnlyResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        /** @var AncientPerson $ap */
        if (!$this->resource || !$this->resource->relationLoaded('ancientPerson') || !$this->resource->ancientPerson) {
            return [];
        }
        $ap = $this->ancientPerson;

        return [
            'id' => $this->ancient_person_id,
            'id_name' => $this->ancient_person_id."_".$ap->getName(),


            'tm_id' => $ap->getTmId(),
            'name' => $ap->getName(),
            'gender' => new IdNameResource($ap->gender),
        ];
    }
}