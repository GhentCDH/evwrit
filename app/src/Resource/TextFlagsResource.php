<?php

namespace App\Resource;


class TextFlagsResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {

        if(is_object($this) && isset($this->needs_attention) && isset($this->review_done)){
            return [
                'needs_attention' => $this->needs_attention,
                'review_done' => $this->review_done,
            ];
        }

        return [
            'needs_attention' =>false,
            'review_done' =>false
        ];

    }
}