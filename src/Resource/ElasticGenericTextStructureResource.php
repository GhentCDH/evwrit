<?php


namespace App\Resource;


use App\Model\AbstractAnnotationModel;
use App\Model\GenericTextStructure;
use App\Model\Text;
use App\Model\TextSelection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JsonSerializable;
use function Symfony\Component\String\u;

class ElasticGenericTextStructureResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        /** @var GenericTextStructure $resource */
        $resource = $this->resource;
        $ret = [
            'id' => $resource->getId(),
            'text_selection' => (new TextSelectionResource($resource->textSelection))->toArray(),
            'part' => (new ElasticIdNameResource($resource->part))->toArray(),
            'part_number' => $resource->partNumber(),
//            'components' => IdNameResource::collection($resource->components),
            'text_level' => $resource->textLevel ? (new ElasticTextLevelResource($resource->textLevel))->toArray() : null
        ];

        return $ret;
    }
}