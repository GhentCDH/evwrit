<?php

namespace App\Resource;


use App\Model\HandshiftAnnotation;

class ElasticHandshiftAnnotationResource extends BaseElasticAnnotationResource
{
    protected bool $allowEmptyRelationProperties = true;

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
        $ret['internal_hand_num'] = $resource->internal_hand_num;
        $ret['ancient_person'] = new AttestationAncientPersonOnlyResource($resource->attestation);

        return $ret;
    }
}