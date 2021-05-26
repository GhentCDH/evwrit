<?php

namespace App\Resource;


class AttestationGraphTypeElasticResource extends BaseResource
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
            'id' => $this->graph_type_id,
            'name' => $this->graphType->name,
            'ancient_person_id' => $this->ancient_person_id,
            'education_id' => $this->education_id,
            'age_id' => $this->age_id,
        ];
    }
}