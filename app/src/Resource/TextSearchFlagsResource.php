<?php

namespace App\Resource;


class TextSearchFlagsResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request = null): array
    {
        $res = [
            'count' => $request,
            'data' => [],

        ];

        return $res;

    }
}