<?php

namespace App\Resource;

use App\Model\TextSelection;
use Illuminate\Http\Request;

/**
 * Class TextSelectionResource
 * @property TextSelection $resource
 * @package App\Resource
 * @mixin TextSelection
 */
class TextSelectionResource extends ElasticBaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        $ret = parent::toArray();
        $ret['text'] = TextSelectionResource::convertNewlines($ret['text']);

        return $ret;
    }

    public static function convertNewlines(?string $text): ?string
    {
        return $text ? str_replace("\v", "\n", $text) : $text;
    }

}