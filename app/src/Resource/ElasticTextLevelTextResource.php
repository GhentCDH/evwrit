<?php

namespace App\Resource;

use App\Model\Text;

/**
 * Class AttestationResource
 * @package App\Resource
 * @mixin Text
 */
class ElasticTextLevelTextResource extends ElasticBaseResource
{
    use TraitTextSelectionIntersect;
    
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        /** @var Text $text */
        $text = $this->resource;

//        $attributes = $text->attributesToArray();

        $attributes = [];

        $ret = array_merge($attributes, [
            /* properties */
            'text_id' => $text->getId(),
            'tm_id' => $text->tm_id,
            'title' => $text->title,

//            'text' => TextSelectionResource::convertNewlines($text->text),
//            'text_lemmas' => TextSelectionResource::convertNewlines($text->text_lemmas),
//            'apparatus' => TextSelectionResource::convertNewlines($text->apparatus),

//            'translation' => TranslationResource::collection($text->translations)->toArray(),

            'era' => new ElasticIdNameResource($text->era),

            'archive' => new ElasticIdNameResource($text->archive),

            'language' => ElasticIdNameResource::collection($text->languages)->toArray(),
            'language_count' => count($text->languages),
            'script' => ElasticIdNameResource::collection($text->scripts)->toArray(),
            'script_count' => count($text->scripts),

            'collaborator' => ElasticIdNameResource::collection($text->collaborators)->toArray(),
            'social_distance' => ElasticIdNameResource::collection($text->socialDistances)->toArray(),
            'project' => ElasticIdNameResource::collection($text->projects)->toArray(),
            'keyword' => ElasticIdNameResource::collection($text->keywords)->toArray(),

            'location_found' => ElasticIdNameResource::collection($text->locationsFound)->toArray(),
            'location_written' => ElasticIdNameResource::collection($text->locationsWritten)->toArray(),

            /* links */
            'image' => ElasticImageResource::collection($text->images)->toArray(),
            'link' => LinkResource::collection($text->links)->toArray(),

            /* materiality */
            'is_recto' => self::boolean($text->is_recto),
            'is_verso' => self::boolean($text->is_verso),
            'is_transversa_charta' => self::boolean($text->is_transversa_charta),
            'tomos_synkollesimos' => self::boolean($text->tomos_synkollesimos),
            'form' => ElasticIdNameResource::collection($text->forms)->toArray(),
            'preservation_state' => ElasticIdNameResource::collection($text->preservationStates),
            'preservation_status_w' => new ElasticIdNameResource($text->preservationStatusW),
            'preservation_status_h' => new ElasticIdNameResource($text->preservationStatusH),

            'lines' => !is_null($text->count_lines) ? [ 'min' => $text->count_lines, 'max' => $text->count_lines ] : ( is_null($text->lines_min) ? null : [ 'min' => $text->lines_min, 'max' => $text->lines_max ] ),
            'columns' => is_null($text->columns_min) ? null : [ 'min' => $text->columns_min, 'max' => $text->columns_max ],
            'letters_per_line' => is_null($text->letters_per_line_min) ? null : [ 'min' => $text->letters_per_line_min, 'max' => $text->letters_per_line_max ],
//            'interlinear_space' => $text->interlinear_space,

            'writing_direction' => ElasticIdNameResource::collection($text->writingDirections)->toArray(),
            'text_format' => new ElasticIdNameResource($text->textFormat),
            'material' => ElasticIdNameResource::collection($text->materials)->toArray(),

            /* global language */
            'drawing' => new ElasticIdNameResource($text->drawing),
            'margin_filler' => new ElasticIdNameResource($text->marginFiller),
            'margin_writing' => new ElasticIdNameResource($text->marginWriting),
        ]);

        return $ret;
    }
}