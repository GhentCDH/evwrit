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

        // context start: if selection start > 0, find last vertical tab in string before selection start
        $c_start = 0;
        if ($s_start) {
            $selection_prefix = mb_substr($text, 0, $s_start);
            $c_start = mb_strrpos($selection_prefix, "\v");
            $c_start = $c_start !== false ? min($t_end, $c_start + 1) : $t_end;
        }

        // context end: find first vertical tab to the right of selection_end
        $c_end = $t_end;
        if ( $s_end < $t_end ) {
            $c_end = mb_strpos($text, "\v", $s_end);
            $c_end = $c_end !== false ? max(0, $c_end - 1) : $t_end;
        }

        // left pos
        $context = [
            'text' => mb_substr($text, $c_start, $c_end - $c_start + 1 ),
            'start' => $c_start,
            'end' => $c_end
        ];

//        print_r($E);
//        print_r($text_selection);
//        echo "t_len: {$t_len}".PHP_EOL;
//        echo "t_end: {$t_len}".PHP_EOL;
//        echo "s_start: {$s_start}".PHP_EOL;
//        echo "s_end: {$s_end}".PHP_EOL;
//        echo "c_start: {$c_start}".PHP_EOL;
//        echo "c_end: {$c_end}".PHP_EOL;
//
//        echo "max(-t_len + s_start, t_end) ".max(-$t_len + $s_start, $t_end).PHP_EOL;
//        die();

        $context['text'] = $this->convertNewlines($context['text']);

        return $context;
    }
}