<?php

namespace App\Resource;

use App\Model\Image;
use Arrayy\Arrayy as A;

/**
 * @mixin Image
 */
class ElasticImageResource extends ElasticBaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        $ret = parent::toArray($request);
        if ($ret === []) {
            return $ret;
        }
        $ret['kollemata'] = A::create(explode('|', $ret['kollemata']))
            ->map(fn($i) => trim($i))
            ->filter(fn($i) => !empty($i))
            ->map(fn($i) => floatval($i))
            ->toArray();

        $ret['kollemata_count'] = count($ret['kollemata']);
        return $ret;
    }
}