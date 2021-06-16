<?php

namespace App\Resource;


class ImageResource extends BaseResource
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
            'id' => $this->image_id,
            'filename' => $this->filename
        ];
    }
}