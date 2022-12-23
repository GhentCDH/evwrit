<?php


namespace App\Resource;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JsonSerializable;

class BaseResource extends AbstractResource
{
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

    /**
     * Create a new anonymous resource collection.
     *
     * @param  mixed  $resource
     * @return BaseResourceCollection
     */
    public static function collection($resource)
    {
        return tap(new BaseResourceCollection($resource, static::class), function ($collection) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true;
            }
        });
    }

}