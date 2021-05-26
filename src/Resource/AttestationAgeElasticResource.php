<?php

namespace App\Resource;


class AttestationAgeElasticResource extends BaseResource
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
            'id' => $this->age_id,
            'name' => $this->age->name,
            'ancient_person_id' => $this->ancient_person_id,
            'education_id' => $this->education_id,
            'graph_type_id' => $this->graph_type_id,
        ];
    }
}