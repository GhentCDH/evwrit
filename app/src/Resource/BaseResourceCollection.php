<?php

namespace App\Resource;

use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class BaseResourceCollection extends \Illuminate\Http\Resources\Json\AnonymousResourceCollection
{

    /**
     * Transform the resource into a JSON array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request = null): array
    {
        return array_filter(
            $this->collection->map->toArray($request)->all(), // todo: check!
            fn($item) => $item,
        );
    }

    /**
     * Prepare the resource for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->resolve();
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