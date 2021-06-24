<?php

namespace App\Resource;


class ElasticIdNameResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request = null): ?array
    {
        if ($this->resource) {
            return [
                'id' => $this->getId(),
                'id_name' => $this->getId()."_".$this->name,
            ];
        }
        return null;
    }
}