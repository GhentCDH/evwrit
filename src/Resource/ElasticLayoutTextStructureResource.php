<?php


namespace App\Resource;


use App\Model\LayoutTextStructure;


class ElasticLayoutTextStructureResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        /** @var LayoutTextStructure $resource */
        $resource = $this->resource;
        $ret = [
            'id' => $resource->getId(),
            'text_selection' => (new TextSelectionResource($resource->textSelection))->toArray(),
            'part' => new IdNameResource($resource->part),
        ];

        return $ret;
    }
}