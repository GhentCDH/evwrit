<?php


namespace App\Resource;


use App\Model\AbstractAnnotationModel;
use App\Model\Text;
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
            'text_selection' => (new TextSelectionResource($resource->textSelection))->toArray(),
            'type' => $type,
            'properties' => [
            ]
        ];

        // add all properties (using relations)
        // skip textSelection relation
        $relations = $resource->getRelations();
        unset($relations['textSelection']);

        foreach( $relations as $name => $model) {
            $ret['properties'][$type.'_'.$name] = (new ElasticIdNameResource($model))->toArray();
        }

        // add context
        $ret['context'] = $this->createTextSelectionContext();

        return $ret;
    }

    /**
     * calculcate text selection context
     * start/end are PHP string offsets (starting with 0)!
     * @return array
     */
    protected function createTextSelectionContext() {
        /** @var Text $resource */
        $resource = $this->resource;
        /** @var TextSelection $text_selection */
        $text_selection = $resource->textSelection;

        $text = $resource->textSelection->sourceText->text;

        // text end
        $t_len = mb_strlen($text);
        $t_end = $t_len - 1;

        // selection start/end
        $s_start = min(max(0, $text_selection->selection_start), $t_end); // fix incorrect selection start
        $s_end = min($text_selection->selection_end, $t_end); // fix incorrect selection end

//        echo "selection: {$s_start} - {$s_end} - {$t_len} \n";

        // context start: if selection start > 0, find first vertical tab to the left of selection_end
        $c_start = 0;
        if ( $s_start && ( ( $pos = mb_strrpos($text, "\v", -$t_len + $s_start)) !== false ) ) {
            $c_start = min($pos + 1, $t_end);
        }

        // context end: find first vertical tab to the right of selection_end
        $c_end = $t_end;
        if ( $s_end < $t_end ) {
            $c_end = mb_strpos($text, "\v", $s_end) ?: $t_end;
        }

//        echo "context: {$c_start} - {$c_end}\n";

        // left pos
        $context = [
            'text' => mb_substr($text, $c_start, $c_end - $c_start + 1 ),
            'start' => $c_start,
            'end' => $c_end
        ];

        $context['text'] = $this->convertNewlines($context['text']);

        return $context;
    }
}