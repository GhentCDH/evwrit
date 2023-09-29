<?php

namespace App\Resource;


class LinkResource extends BaseResource
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
            'id' => $this->url_id,
            'url' => $this->url,
            'title' => $this->title
        ];
    }
}