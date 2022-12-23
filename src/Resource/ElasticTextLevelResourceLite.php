<?php


namespace App\Resource;


use App\Model\Level;

/**
 * @mixin Level
 */
class ElasticTextLevelResourceLite extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        /** @var Level $resource */
        $resource = $this->resource;
        $ret = [
            'number' => $resource->number,
        ];

        return $ret;
    }
}