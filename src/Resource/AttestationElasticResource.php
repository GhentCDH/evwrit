<?php

namespace App\Resource;


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
        return [
            'id' => $this->ancient_person_id,
            'name' => $this->ancientPerson->name,
            'role' => IdNameElasticResource::collection($this->roles)->toArray(0),
            'education' => $this->education_id ? new IdNameElasticResource($this->education) : null,
            'age' => $this->age_id ? new IdNameElasticResource($this->age) : null,
        ];
    }
}