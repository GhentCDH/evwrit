<?php


namespace App\Resource;


use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class BaseResource extends AbstractResource
{

    public function toJson($options = 0): ?string
    {
        $data = $this->toArray(null);
        /*
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } elseif ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }
        */

        $data = $this->filter((array) $data);
        return json_encode($data);
    }
}