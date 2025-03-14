<?php /** @noinspection ALL */


namespace App\Resource;


use App\Model\Level;
use App\Model\Text;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use Arrayy\Arrayy as A;

/**
 * @mixin Level
 */
class ElasticTextLevelIndexResource extends ElasticBaseResource
{
    use TraitTextSelectionIntersect;

    private Text $text;

    public function __construct($resource, Text $text)
    {
        parent::__construct($resource);
        $this->text = $text;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        /** @var Level $level */
        $level = $this->resource;
        $text = $this->text;

        $levelProperties = [
            // level properties
            'number' => $level->number,
            'level_category' => ElasticLevelCategoryResource::collection($level->levelCategories)->toArray(),

            'agentive_role' => ElasticAgentiveRoleResource::collection($level->agentiveRoles)->toArray(),
            'communicative_goal' => ElasticCommunicativeGoalResource::collection($level->communicativeGoals)->toArray(),

            'production_stage' => ElasticIdNameResource::collection($level->productionStages)->toArray(),

            'attestations' => ElasticAttestationResource::collection($level->attestations)->toArray(),

//            'physical_objects' => ElasticIdNameResource::collection($level->physicalObjects)->toArray(),
            'greek_latin' => ElasticBaseResource::collection($level->greekLatins)->toArray(),


            // todo: add general information
            // title, text_id, tm_id, year_from, year_to, era, location found, location_written, language, script, keyword, archive, social distance,
            // todo: add materiality
            // production stage, material, format, writing direction, recto, verso, transversa charta, text lines, text columns, letters per line, width, height
        ];

        // generic/layout text structure

        $gts = ElasticGenericTextStructureResource::collection(
            $text->genericTextStructures->filter(fn(\App\Model\GenericTextStructure $gts) => $gts->level_id = $level->level_id)
        )->toArray();
        $gts_ids = A::create($gts)->map(fn($gtsi) => $gtsi['id'])->toArray();

        $gtsa = ElasticGenericTextStructureAnnotationResource::collection(
            $text->genericTextStructureAnnotations
                ->filter( fn($a) => in_array($a->generic_text_structure_id, $gts_ids))
                ->map( fn($a) => new ElasticGenericTextStructureAnnotationResource($a, $text))
        )->toArray();

        // layout text structure
        $lts = ElasticLayoutTextStructureResource::collection($text->layoutTextStructures)->toArray();

        $ltsa = ElasticLayoutTextStructureAnnotationResource::collection(
            $text->layoutTextStructureAnnotations->map( fn($a) => new ElasticLayoutTextStructureAnnotationResource($a, $text) )
        )->toArray();

        // handshift
        $hsa = ElasticHandshiftAnnotationResource::collection(
            $text->handshiftAnnotations->map( fn($a) => new ElasticHandshiftAnnotationResource($a, $text) )
        )->toArray();

//        dump($gts);
//        dump($lts);
//        dump($ltsa);

        /// if level number === 0, don't filter lts, ltsa and hsa
        /// if level number !== 0, check overlap with gts
        if ($level->number !== 0) {
            $lts = array_filter($lts, function($lts_item) use ($gts) {
                foreach($gts as $gts_item) {
                    if ( $this->textSelectionIntersect($lts_item, $gts_item) ) {
                        return true;
                    }
                }
                return false;
            });
            $hsa = array_filter($hsa, function($hsa_item) use ($gts) {
                foreach($gts as $gts_item) {
                    if ( $this->textSelectionIntersect($hsa_item, $gts_item) ) {
                        return true;
                    }
                }
                return false;
            });

            $lts_ids = array_reduce($lts, function($carry, $lts_item) {
                $carry[$lts_item['id']] = true;
                return $carry;
            }, []);

            $ltsa = array_filter($ltsa, function($ltsa_item) use ($lts_ids) {
                return isset($lts_ids[$ltsa_item['layout_text_structure_id']]);
            });

        }

        $ret['has_generic_text_structure'] = self::boolean(count($gts) > 0);
        $ret['has_layout_text_structure'] = self::boolean(count($lts) > 0);

        // intersect generic text structure annotations with lts, gts, ltsa, handshift
        foreach( $gtsa as &$annotationSource ) {
            $this->annotationIntersect($annotationSource, $ltsa);
            $this->annotationIntersect($annotationSource, $hsa);
            $this->annotationIntersect($annotationSource, $gts);
            $this->annotationIntersect($annotationSource, $lts);
            $this->annotationIntersect($annotationSource, $gtsa);
        }
        // intersect layout text structure annotations with gts, lts, gtsa and handshift
        foreach( $ltsa as &$annotationSource ) {
            $this->annotationIntersect($annotationSource, $gtsa);
            $this->annotationIntersect($annotationSource, $hsa);
            $this->annotationIntersect($annotationSource, $gts);
            $this->annotationIntersect($annotationSource, $lts);
            $this->annotationIntersect($annotationSource, $ltsa);
        }

        // interset generic text structure  with lts and handshift
        foreach( $gts as &$annotationSource ) {
            $this->annotationIntersect($annotationSource, $lts);
            $this->annotationIntersect($annotationSource, $hsa);
            $this->annotationIntersect($annotationSource, $gts);
        }

        // interset layout text structure  with gts and handshift
        foreach( $lts as &$annotationSource ) {
            $this->annotationIntersect($annotationSource, $gts);
            $this->annotationIntersect($annotationSource, $hsa);
            $this->annotationIntersect($annotationSource, $lts);
        }

        $levelProperties['annotations'] = array_merge(
            $gts, $gtsa, $lts, $ltsa, $hsa
        );

        // text properties
        $textProperties = (new ElasticTextLevelTextResource($this->text))->toArray();

        return array_merge($levelProperties, $textProperties);
    }
}