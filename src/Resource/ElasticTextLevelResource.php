<?php


namespace App\Resource;


use App\Model\TextLevel;

/**
 * @mixin TextLevel
 */
class ElasticTextLevelResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        /** @var TextLevel $resource */
        $resource = $this->resource;
        $ret = [
            'number' => $resource->number,
            'type' => $resource->type,
        ];

        return $ret;
    }
}