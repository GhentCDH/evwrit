<?php


namespace App\Resource;


use Illuminate\Http\Resources\Json\JsonResource;

class AbstractResource extends JsonResource
{
    // Self-referential 'abstract' declaration
    const CACHENAME = self::class;

    public final function getId() {
        return $this->resource->getId();
    }
}