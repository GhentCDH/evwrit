<?php


namespace App\Resource;


use Illuminate\Http\Resources\Json\JsonResource;

class AbstractResource extends JsonResource
{
    public final function getId() {
        return $this->resource->getId();
    }
}