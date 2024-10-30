<?php


namespace App\Resource;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class BaseResource extends JsonResource
{
    public function getId(): string
    {
        return $this->resource->getId();
    }

    /**
     * Transform the resource into a JSON array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request = null): array
    {
        return (array) parent::toArray($request);
    }

    public function jsonSerialize(): array
    {
        return $this->resolve();
    }

    /**
     * Resolve the resource to an array.
     *
     * @param Request|null  $request
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

    /**
     * Create a new anonymous resource collection.
     *
     * @param  mixed  $resource
     * @return BaseResourceCollection
     */
    public static function collection($resource): BaseResourceCollection
    {
        return tap(new BaseResourceCollection($resource, static::class), function ($collection) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true;
            }
        });
    }

}