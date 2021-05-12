<?php

namespace App\Resource;


class TextElasticResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->getId(),
            'tm_id' => $this->tm_id,
            'title' => $this->title,
            'text' => $this->text,
            'year_begin' => $this->year_begin,
            'year_end' => $this->year_end,
            'era' => (new IdNameResource($this->era))->toArray(),
            'archive' => (new IdNameResource($this->archive))->toArray(),


            'script' => IdNameResource::collection($this->scripts)->toArray(null),
            'form' => IdNameResource::collection($this->forms)->toArray(null),
            'material' => IdNameResource::collection($this->materials)->toArray(null),
            'social_distance' => IdNameResource::collection($this->socialDistances)->toArray(null),
            'project' => IdNameResource::collection($this->projects)->toArray(null),
        ];
    }
}