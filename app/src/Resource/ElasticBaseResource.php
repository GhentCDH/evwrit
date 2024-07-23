<?php


namespace App\Resource;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JsonSerializable;

class ElasticBaseResource extends BaseResource
{
    protected static function boolean($value): ?string
    {
        return $value ? 'true' : null;
    }

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
        if (isset($ret[$keyName])) {
            $ret['id'] = $ret[$keyName];
            unset($ret[$keyName]);
        }

        return $ret;
    }
}