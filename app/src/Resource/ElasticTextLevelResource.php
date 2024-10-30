<?php /** @noinspection ALL */


namespace App\Resource;


use App\Model\Level;

/**
 * @mixin Level
 */
class ElasticTextLevelResource extends ElasticBaseResource
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
        /** @var Level $level */
        $level = $this->resource;

        $levelProperties = [
            // level properties
            'id' => $level->getId(),
            'number' => $level->number,

            'level_category' => ElasticLevelCategoryResource::collection($level->levelCategories)->toArray(),
            'agentive_role' => ElasticAgentiveRoleResource::collection($level->agentiveRoles)->toArray(),
            'communicative_goal' => ElasticCommunicativeGoalResource::collection($level->communicativeGoals)->toArray(),
            'production_stage' => ElasticIdNameResource::collection($level->productionStages)->toArray(),
            'attestations' => ElasticAttestationResource::collection($level->attestations)->toArray(),
//            'physical_object' => ElasticIdNameResource::collection($level->physicalObjects)->toArray(),
            'greek_latin' => ElasticBaseResource::collection($level->greekLatins)->toArray(),
        ];

        return $levelProperties;
    }
}