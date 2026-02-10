<?php


namespace App\Resource;


use App\Model\AbstractAnnotationModel;
use App\Model\IdNameModel;
use App\Model\Text;

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
        $this->text = $text ?? $resource?->textSelection?->sourceText ?? null;
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
        $textSelection = (new TextSelectionResource($resource->textSelection))->toArray();

        $ret = [
            'id' => $resource->getId(),
            'text_selection' => $textSelection,
            'type' => $type,
            'properties' => [
            ],
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
            $ret['context'] = $this->createTextSelectionContext($this->text->text, $textSelection['selection_start'], $textSelection['selection_end']);
        }

        return $ret;
    }

    /**
     * calculate text selection context
     * database offsets are 1-based, end offset not inclusive, length should be (end - start)
     */
    protected function createTextSelectionContext(string $text, int $selectionStart, int $selectionEnd, int $offset = 1, bool $endInclusive = false): array {

//        dump([$selectionStart, $selectionEnd]);

        // convert selection start/end to 0-based inclusive offsets
        $selectionStart -= $offset;
        $selectionEnd -= $offset;
        if (!$endInclusive) {
            $selectionEnd -= 1;
        }

        // text end
        $textLength = mb_strlen($text);
        $textEnd = $textLength - 1;

        // selection start/end
        $selectionStart = min(max(0, $selectionStart), $textEnd); // fix incorrect selection start
        $selectionEnd = min(max(0,$selectionEnd), $textEnd); // fix incorrect selection end

//        dump([$selectionStart, $selectionEnd]);

        // context start: if selection start > 0, find last vertical tab in string before selection start
        $contextStart = 0;
        if ($selectionStart) {
            $selectionPrefix = mb_substr($text, 0, $selectionStart); // extract string before selection start
            $contextStart = mb_strrpos($selectionPrefix, "\v");
            $contextStart = $contextStart !== false ? min($textEnd, $contextStart + 1) : 0;
        }

        // context end: find first vertical tab to the right of selection_end
        $contextEnd = $textEnd;
        if ( $selectionEnd < $textEnd ) {
            $contextEnd = mb_strpos($text, "\v", $selectionEnd);
            $contextEnd = $contextEnd !== false ? max(0, $contextEnd - 1) : $textEnd;
        }

//        dump([$selectionStart, $selectionEnd, $contextStart, $contextEnd]);

        // create context array
        $context = [
            'text' => mb_substr($text, $contextStart, $contextEnd - $contextStart + 1 ),
            'start' => $contextStart + $offset,
            'end' => $contextEnd + $offset + ($endInclusive ? 0 : 1),
        ];

        $context['text'] = TextSelectionResource::convertNewlines($context['text']);

        return $context;
    }
}