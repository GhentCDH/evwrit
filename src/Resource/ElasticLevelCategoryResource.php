<?php

namespace App\Resource;


use App\Model\AgentiveRole;
use App\Model\LevelCategory;

class ElasticLevelCategoryResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null)
    {
        /** @var LevelCategory $resource */
        $resource = $this->resource;
        return [
            'id' => $this->resource->getId(),
            'level_category_category' => new ElasticIdNameResource($resource->levelCategory),
            'level_category_subcategory' => new ElasticIdNameResource($resource->levelSubcategory),
            'level_category_hypercategory' => new ElasticIdNameResource($resource->levelHypercategory),
        ];
    }
}