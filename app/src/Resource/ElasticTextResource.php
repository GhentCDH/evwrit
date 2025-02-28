<?php

namespace App\Resource;

use App\Model\Text;

/**
 * Class AttestationResource
 * @package App\Resource
 * @mixin Text
 */
class ElasticTextResource extends ElasticBaseResource
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

        $attributes = $text->attributesToArray();

        $ret = array_merge($attributes, [
            /* properties */
            'id' => $text->getId(),
            'text' => TextSelectionResource::convertNewlines($text->text),
            'text_lemmas' => TextSelectionResource::convertNewlines($text->text_lemmas),
            'text_edited' => TextSelectionResource::convertNewlines($text->text_edited),
            'apparatus' => TextSelectionResource::convertNewlines($text->apparatus),

            'translation' => TranslationResource::collection($text->translations)->toArray(),

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
            'total_area' => $text->width !=0 && $text->height != 0 ? $text->width * $text->height : null,
            'central_width' => $text->width - $text->margin_left - $text->margin_right,
            'central_height' => $text->height - $text->margin_top - $text->margin_bottom,

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

            // annotations placeholder
            'annotations' => []
        ]);

        $ret['used_area'] = $ret['central_width'] * $ret['central_height'];
        $ret['whitespace_area'] = $ret['total_area'] - $ret['used_area'];
        $ret['whitespace_percentage'] = $ret['whitespace_area'] != 0 && $ret['total_area'] != 0 ? $ret['whitespace_area'] / $ret['total_area'] * 100 : null;

        $ret['width_height_ratio'] = $text->width && $text->height ? $text->width / $text->height: null;
        $ret['lineheight_interlinearspace_ratio'] = count($text->images) > 0 ? $text->images[0]->line_height && $text->interlinear_space ? $text->images[0]->line_height / $text->interlinear_space : null : null;

        // add line count
        $ret['line_count'] = $ret['text'] ? count(explode("\n",$ret['text'])) : 0;

        $ret['average_line_space'] = $ret['line_count'] != 0 ? $ret['used_area'] / $ret['line_count'] : null;
        $ret['average_words_per_line'] = $text->count_words && $ret['line_count'] ?   $text->count_words / $ret['line_count'] : null;
        $ret['average_letter_space'] = $text->letters_per_line_auto && $ret['line_count'] ? $ret['used_area'] / $ret['line_count'] * $text->letters_per_line_auto : null;

            // flatten level properties
        // todo: fix legacy 'ancient_person'
        $textLevels = ElasticTextLevelResource::collection($text->textLevels)->toArray();
        $textLevelProperties = array_merge_recursive(...$textLevels);
        unset($textLevelProperties['number']);
        unset($textLevelProperties['id']);
        $textLevelProperties['ancient_person'] = $textLevelProperties['attestations'] ?? null;
        unset($textLevelProperties['attestations']);

        $ret = array_merge($ret, $textLevelProperties);

        // base annotations
        $ret['annotations'] = array_merge(
            BaseElasticAnnotationResource::collection(
                $text->languageAnnotations->map( fn($a) => new BaseElasticAnnotationResource($a, $text) )
            )->toArray(),
            BaseElasticAnnotationResource::collection(
                $text->typographyAnnotations->map( fn($a) => new BaseElasticAnnotationResource($a, $text) )
            )->toArray(),
            BaseElasticAnnotationResource::collection(
                $text->lexisAnnotations->map( fn($a) => new BaseElasticAnnotationResource($a, $text) )
            )->toArray(),
            BaseElasticAnnotationResource::collection(
                $text->orthographyAnnotations->map( fn($a) => new BaseElasticAnnotationResource($a, $text) )
            )->toArray(),
            BaseElasticAnnotationResource::collection(
                $text->morphologyAnnotations->map( fn($a) => new BaseElasticAnnotationResource($a, $text) )
            )->toArray(),
            BaseElasticAnnotationResource::collection(
                $text->morphoSyntacticalAnnotations->map( fn($a) => new BaseElasticAnnotationResource($a, $text) )
            )->toArray(),
        );

        // handshift annotations
        $handshiftAnnotations = ElasticHandshiftAnnotationResource::collection(
            $text->handshiftAnnotations->map( fn($a) => new ElasticHandshiftAnnotationResource($a, $text) )
        )->toArray();

        // generic/layout text structure
        $genericTextStructure = ElasticGenericTextStructureResource::collection(
            $text->genericTextStructures
        )->toArray();
        $ret['has_generic_text_structure'] = self::boolean(count($genericTextStructure) > 0);

        $layoutTextStructure = ElasticLayoutTextStructureResource::collection(
            $text->layoutTextStructures
        )->toArray();
        $ret['has_layout_text_structure'] = self::boolean(count($layoutTextStructure) > 0);

        // text levels
        $ret['text_level'] = ElasticTextLevelResource::collection($text->textLevels)->toArray();

        // generic/layout text structure annotations
        $gtsAnnotations = ElasticGenericTextStructureAnnotationResource::collection(
            $text->genericTextStructureAnnotations->map( fn($a) => new ElasticGenericTextStructureAnnotationResource($a, $text) )
        )->toArray();
        $ltsAnnotations = ElasticLayoutTextStructureAnnotationResource::collection(
            $text->layoutTextStructureAnnotations->map( fn($a) => new ElasticLayoutTextStructureAnnotationResource($a, $text) )
        )->toArray();

        // calculate base annotations intersect with text_structure and handshift
        foreach($ret['annotations'] as &$annotationSource) {
            $this->annotationIntersect($annotationSource, $genericTextStructure ?? [], ['gts_part', 'gts_textLevel']);
            $this->annotationIntersect($annotationSource, $gtsAnnotations ?? [], ['gtsa_type', 'gtsa_subtype', 'gtsa_speechAct']);
            $this->annotationIntersect($annotationSource, $handshiftAnnotations);
            $this->annotationIntersect($annotationSource, $layoutTextStructure ?? []);
        }

        // intersections below not needed for search, but needed for the text viewer

        // intersect generic text structure annotations with lts, gts, ltsa, handshift
        foreach( $gtsAnnotations as &$annotationSource ) {
            $this->annotationIntersect($annotationSource, $ltsAnnotations);
            $this->annotationIntersect($annotationSource, $handshiftAnnotations);
            $this->annotationIntersect($annotationSource, $genericTextStructure);
            $this->annotationIntersect($annotationSource, $layoutTextStructure);
        }
        // intersect layout text structure annotations with gts, lts, gtsa and handshift
        foreach( $ltsAnnotations as &$annotationSource ) {
            $this->annotationIntersect($annotationSource, $gtsAnnotations);
            $this->annotationIntersect($annotationSource, $handshiftAnnotations);
            $this->annotationIntersect($annotationSource, $genericTextStructure);
            $this->annotationIntersect($annotationSource, $layoutTextStructure);
        }

        // interset generic text structure  with lts and handshift
        foreach( $genericTextStructure as &$annotationSource ) {
            $this->annotationIntersect($annotationSource, $layoutTextStructure);
            $this->annotationIntersect($annotationSource, $handshiftAnnotations);
        }

        // interset layout text structure  with gts and handshift
        foreach( $layoutTextStructure as &$annotationSource ) {
            $this->annotationIntersect($annotationSource, $genericTextStructure);
            $this->annotationIntersect($annotationSource, $handshiftAnnotations);
        }

        $ret['annotations'] = array_merge(
            $ret['annotations'],
            $gtsAnnotations,
            $ltsAnnotations,
            $genericTextStructure,
            $layoutTextStructure,
            $handshiftAnnotations
        );

        return $ret;
    }
}