<?php

namespace App\Resource;


use App\Model\Occupation;

class ElasticOccupationResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        /** @var Occupation $resource */
        $resource = $this->resource;

        if (!$this->resource) {
            return [];
        }

        return [
            'id' => $resource->getId(),
            'name' => [
                'en' => $resource->name_en,
                'gr' => $resource->name_gr,
            ],
            'id_name' => [
                'en' => $resource->getId()."_".$resource->name_en,
                'gr' => $resource->getId()."_".$resource->name_gr
            ]
        ];
    }
}