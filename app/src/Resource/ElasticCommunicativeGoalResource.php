<?php

namespace App\Resource;

use App\Model\CommunicativeGoal;

/**
 * @property CommunicativeGoal $resource
 */
class ElasticCommunicativeGoalResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        return [
            'id' => $this->resource->getId(),
            'communicative_goal_subtype' => new ElasticIdNameResource($this->resource->communicativeGoalSubtype),
            'communicative_goal_type' => new ElasticIdNameResource($this->resource->communicativeGoalType),
        ];
    }
}