<?php

namespace App\Resource;


class CommunicativeGoalElasticResource extends BaseResource
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
            'generic_communicative_goal' => new ElasticIdNameResource($this->genericCommunicativeGoal),
            'communicative_goal' => new ElasticIdNameResource($this->communicativeGoal),
        ];
    }
}