<?php

namespace App\Resource;


use App\Model\IdNameModelInterface;

class ElasticIdNameResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request = null): array
    {
        /** @var IdNameModelInterface $resource */
        $resource = $this->resource;

        if (!$this->resource) {
            return [];
        }

        return [
            'id' => $resource->getId(),
            'name' => $resource->getName(),
            'id_name' => $resource->getId()."_".$resource->getName(),
        ];
    }

}