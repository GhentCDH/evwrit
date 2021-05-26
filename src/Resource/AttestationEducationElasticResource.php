<?php

namespace App\Resource;


class AttestationEducationElasticResource extends BaseResource
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
            'id' => $this->education_id,
            'name' => $this->education->name,
            'ancient_person_id' => $this->ancient_person_id,
            'age_id' => $this->age_id,
            'graph_type_id' => $this->graph_type_id,
        ];
    }
}