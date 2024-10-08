<?php

namespace App\Resource;

use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Arrayable;

class BaseResourceCollection extends \Illuminate\Http\Resources\Json\AnonymousResourceCollection
{

    /**
     * Transform the resource into a JSON array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request = null): array
    {
        return array_filter(
            $this->collection->map->toArray($request)->all(),
            fn($item) => $item,
        );
    }

    /**
     * Prepare the resource for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->resolve(null);
    }

    /**
     * Resolve the resource to an array.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array
     */
    public function resolve($request = null): array
    {
        $data = $this->toArray();

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } elseif ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        return $this->filter((array) $data);
    }
}