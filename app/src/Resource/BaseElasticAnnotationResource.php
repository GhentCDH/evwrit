<?php


namespace App\Resource;


use App\Model\AbstractAnnotationModel;
use App\Model\IdNameModel;
use App\Model\Text;
use App\Model\TextSelection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JsonSerializable;
use function Symfony\Component\String\u;

class BaseElasticAnnotationResource extends BaseResource
{
    use TraitAnnotationOverride;

    protected array $includeAttributes = [];
    protected bool $generateContext = true;
    protected bool $allowEmptyRelationProperties = false;

    protected array $skipRelations = [
        'aspectContent','aspectContext','aspectForm',
        'modalityContent','modalityContext','modalityForm',
        'cliticContent','cliticContext','cliticForm',
        'caseContent','caseContext','caseForm',
    ];

    private ?Text $text;

    public function __construct($resource, Text $text = null)
    {
        parent::__construct($resource);
        $this->text = $text ?? $resource?->textSelection?->sourceText?->text ?? null;
    }

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



        // add overrides
        $ret = $this->override($ret, $resource);

        // skip deleted records
        if ($ret['isDeleted'] ?? null) {
            return [];
        }

        // add all id_name lookups
        $relations = $resource->getRelations();
        if ( count($relations) ) {
            // add relation to annotation properties
            foreach( $relations as $name => $model) {
                if ( in_array($name, $this->skipRelations) ) {
                    continue;
                }
                if (is_subclass_of($model, IdNameModel::class)) {
                    $ret['properties'][$type.'_'.$name] = (new ElasticIdNameResource($model))->toArray();
                }
            }

            // no properties? skip this record
            if (!$this->allowEmptyRelationProperties && !count($ret['properties'])) {
                return [];
            }
        }

        // add all extra properties
        foreach($this->includeAttributes as $attribute ) {
            $attributeValue = $resource->getAttribute($attribute);
            if ( is_object($attributeValue) ) {
                if (is_subclass_of($attributeValue, IdNameModel::class)) {
                    $ret['properties'][$type . '_' . $attribute] = (new ElasticIdNameResource($attributeValue))->toArray();
                }
            } else {
                $ret['properties'][$type . '_' . $attribute] = $attributeValue;
            }
        }

        // generate text selection context?
        if ( $this->generateContext && $this->text ) {
            $ret['context'] = $this->createTextSelectionContext($this->text->text, $resource->textSelection);
        }

        return $ret;
    }

    /**
     * calculate text selection context
     * start/end are PHP string offsets (starting with 0)!
     */
    protected function createTextSelectionContext(string $text, TextSelection $textSelection): array {

        // text end
        $t_len = mb_strlen($text);
        $t_end = $t_len - 1;

        // selection start/end
        $s_start = min(max(0, $textSelection->selection_start), $t_end); // fix incorrect selection start
        $s_end = min($textSelection->selection_end, $t_end); // fix incorrect selection end

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

        $context['text'] = TextSelectionResource::convertNewlines($context['text']);

        return $context;
    }
}