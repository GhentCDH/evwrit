<?php

namespace App\Resource;


class TextResource extends BaseResource
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
            'id' => $this->getId(),
            'title' => $this->title,
//            'text' => $this->text,
            'script' => $this->scripts->modelKeys(),
            'form' => $this->forms->modelKeys(),
//            'material' => $this->materials->modelKeys(),
//            'social_distance' => $this->socialDistances->modelKeys()
        ];
    }
}