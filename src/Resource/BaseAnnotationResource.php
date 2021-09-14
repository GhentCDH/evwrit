<?php


namespace App\Resource;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JsonSerializable;
use function Symfony\Component\String\u;

class BaseAnnotationResource extends BaseResource
{
    protected const annotationLookupModels = [];

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        $ret = [
            'id' => $this->getId(),
            'text_selection' => new TextSelectionResource($this->textSelection),
            'annotation' => [
            ]
        ];

        foreach( static::annotationLookupModels as $prop) {
            $prop = u($prop)->replace('Annotation','');
            $relation = $prop->camel();
            $ret['annotation'][$prop->snake()->toString()] = new IdNameResource($this->$relation);
        }

        return $ret;
    }
}