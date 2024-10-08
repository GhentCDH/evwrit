<?php

namespace App\Resource;

use App\Model\TextSelection;

/**
 * Class TextSelectionResource
 * @property TextSelection $resource
 * @package App\Resource
 * @mixin TextSelection
 */
class TextSelectionResource extends BaseResource
{
    const CACHENAME = "text_selection_resource";

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        if (!$this->resource) {
            return [];
        }
        $ret = $this->resource->attributesToArray();
        $keyName = $this->resource->getKeyName();
        if ( isset($ret[$keyName]) ) {
            $ret['id'] = $ret[$keyName];
            unset($ret[$keyName]);
        }

        // convert newlines
        $ret['text'] = TextSelectionResource::convertNewlines($ret['text']);

        return $ret;
    }

    public static function convertNewlines(?string $text): ?string
    {
        return $text ? str_replace("\v", "\n", $text) : $text;
    }

}