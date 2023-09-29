<?php

namespace App\Resource;

use App\Model\Image;

/**
 * @mixin Image
 */
class ImageResource extends BaseResource
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
            'id' => $this->image_id,
            'filename' => $this->filename,
            'copyright' => $this->copyright,
            'source' => $this->source
        ];
    }
}