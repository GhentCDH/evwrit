<?php

namespace App\Resource;


use App\Model\HandshiftAnnotation;

class ElasticHandshiftAnnotationResource extends BaseElasticAnnotationResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request = null): array
    {
        /** @var HandshiftAnnotation $resource */
        $resource = $this->resource;

        $ret = parent::toArray($request);

        return $ret;
    }
}