<?php


namespace App\Resource;


use App\Model\LayoutTextStructure;


class ElasticLayoutTextStructureResource extends BaseResource
{
    use TraitAnnotationOverride;

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
            'type' => 'lts',
            'properties' => [
                'lts_part' => (new ElasticIdNameResource($resource->part))->toArray(),
                'lts_partNumber' => $resource->partNumber,
                'lts_preservationStatus' => $resource->preservationStatus,
            ]
        ];

        // add overrides
        $ret = $this->override($ret, $resource);

        // skip deleted records?
//        if ($ret['isDeleted'] ?? null) {
//            return [];
//        }

        return $ret;
    }
}