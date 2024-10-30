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
    use TraitAnnotationOverride;
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
            'type' => 'gts',
            'properties' => [
                'gts_part' => (new ElasticIdNameResource($resource->part))->toArray(),
                'gts_partNumber' => $resource->partNumber,
//            'components' => IdNameResource::collection($resource->components),
                'gts_textLevel' => $resource->textLevel ? (new ElasticTextLevelResourceLite($resource->textLevel))->toArray() : null,
                'gts_preservationStatus' => $resource->preservationStatus,
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