<?php

namespace App\Resource;


use App\Model\AgentiveRole;

class ElasticAgentiveRoleResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null)
    {
        /** @var AgentiveRole $resource */
        $resource = $this->resource;
        return [
            'generic_agentive_role' => new ElasticIdNameResource($resource->genericAgentiveRole),
            'agentive_role' => new ElasticIdNameResource($resource),
        ];
    }
}