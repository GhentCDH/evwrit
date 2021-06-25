<?php

namespace App\Resource;


class AgentiveRoleElasticResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null)
    {
        return [
            'generic_agentive_role' => new ElasticIdNameResource($this->genericAgentiveRole),
            'agentive_role' => new ElasticIdNameResource($this->agentiveRole),
        ];
    }
}