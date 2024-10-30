<?php

namespace App\Resource;


use App\Model\GreekLatin;
use App\Model\IdNameModelInterface;

class ElasticGreekLatinResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request = null): array
    {
        /** @var GreekLatin $resource */
        $resource = $this->resource;

        if (!$this->resource) {
            return [];
        }

        return [
            'id' => $resource->getId(),
            'label' => $resource->label,
            'sublabel' => $resource->sublabel,
            'english' => $resource->english,
        ];
    }

}