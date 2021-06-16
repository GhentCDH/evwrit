<?php

namespace App\Resource;


class TextResource extends BaseResource
{
    const CACHENAME = "text";

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request=null): array
    {
        return [
            'id' => $this->getId(),
            'tm_id' => $this->tm_id,
            'title' => $this->title,
            'text' => $this->convertNewlines($this->text),
            'apparatus' => $this->convertNewlines($this->text),

            'year_begin' => $this->year_begin,
            'year_end' => $this->year_end,

            'margin_top' => $this->margin_top,
            'margin_left' => $this->margin_left,
            'margin_right' => $this->margin_right,
            'margin_bottom' => $this->margin_bottom,
            'width' => $this->width,
            'height' => $this->height,

            'materials' => IdNameResource::collection($this->materials)->toArray(null),
            'format' => new IdNameResource($this->textFormat),


            'era' => new IdNameResource($this->era),
            'archive' => new IdNameResource($this->archive),

            'languages' => IdNameResource::collection($this->languages)->toArray(null),

            'text_type' => new IdNameResource($this->textType),
            'text_subtype' => new IdNameResource($this->textSubtype),

            'collaborator' => IdNameResource::collection($this->collaborators)->toArray(null),
            'social_distance' => IdNameResource::collection($this->socialDistances)->toArray(null),
            'projects' => IdNameResource::collection($this->projects)->toArray(null),
            'keywords' => IdNameResource::collection($this->keywords)->toArray(null),

            'location_found' => IdNameResource::collection($this->locationsFound)->toArray(null),
            'location_written' => IdNameResource::collection($this->locationsWritten)->toArray(null),

            'agentive_role' => AgentiveRoleElasticResource::collection($this->textAgentiveRoles)->toArray(null),
            'communicative_goal' => CommunicativeGoalElasticResource::collection($this->textCommunicativeGoals)->toArray(null),

            'translations' => TranslationResource::collection($this->translations)->toArray(null),
            'images' => ImageResource::collection($this->images)->toArray(null),
            'links' => LinkResource::collection($this->links)->toArray(null),
        ];
    }
}