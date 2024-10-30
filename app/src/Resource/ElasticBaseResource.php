<?php


namespace App\Resource;


use Illuminate\Http\Request;

class ElasticBaseResource extends BaseResource
{
    protected static function boolean($value): ?string
    {
        return $value ? 'true' : null;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
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
            $id = $ret[$keyName];
            unset($ret[$keyName]);
            $ret = array_merge(['id' => $id], $ret);
        }

        return $ret;
    }
}