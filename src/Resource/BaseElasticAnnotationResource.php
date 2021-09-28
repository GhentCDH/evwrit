<?php


namespace App\Resource;


use App\Model\AbstractAnnotationModel;
use App\Model\TextSelection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JsonSerializable;
use function Symfony\Component\String\u;

class BaseElasticAnnotationResource extends BaseResource
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
        /** @var AbstractAnnotationModel $resource */
        $resource = $this->resource;
        $type = $resource->getAnnotationType();
        $ret = [
            'id' => $resource->getId(),
            'text_selection' => new TextSelectionResource($resource->textSelection),
            'type' => $type,
            'properties' => [ $type => []
            ]
        ];

        foreach( $resource->getRelations() as $name => $model) {
            $ret['properties'][$name] = new ElasticIdNameResource($model);
        }

        $ret['context'] = $this->createTextSelectionContext();

        return $ret;
    }

    protected function createTextSelectionContext() {
        $resource = $this->resource;
        $text_selection = $resource->textSelection;
        $text = $resource->textSelection->sourceText->text;

        /** @var TextSelection $text_selection */
        $nstart = mb_strrpos($text, "\v", -mb_strlen($text) + $text_selection->selection_start + 1) ?: 0;
        $nstart && $nstart++;
        $nend = -1 + ( mb_strpos($text, "\v", $text_selection->selection_end) ?: strlen($text) );

        // left pos
        $context = [
            'text' => mb_substr($text, $nstart, $nend - $nstart + 1 ),
            'start' => $nstart,
            'end' => $nend
        ];

        return $context;
    }
}