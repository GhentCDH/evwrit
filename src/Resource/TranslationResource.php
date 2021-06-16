<?php

namespace App\Resource;


class TranslationResource extends BaseResource
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
            'text' => $this->text,
            'language' => [
                'id' => $this->iso_language_id,
                'name' => $this->iso_language_name,
            ]
        ];
    }
}