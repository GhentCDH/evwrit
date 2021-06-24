<?php


namespace App\Resource;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JsonSerializable;

class ElasticBaseResource extends BaseResource
{
    protected static function boolean($value)
    {
        return $value ? 'true' : null;
    }
}