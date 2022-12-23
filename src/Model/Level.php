<?php

namespace App\Model;


use App\Model\Lookup\ProductionStage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use ReflectionException;

/**
 * Class TextTranslation
 *
 * @property int $level_id
 * @property int $text_id
 * @property int $number
 * @property string $attested_in_text
 *
 * @property Text $text
 * @property ProductionStage[] $productionStages
 * @property CommunicativeGoal[] $communicativeGoals
 * @property AgentiveRole[] $agentiveRoles
 * @property LevelCategory[] $levelCategories
 * @property PhysicalObject[] $physicalObjects
 * @property GreekLatin[] $greekLatins
 * @property Attestation[] $attestations
 * @property GenericTextStructure[] $genericTextStructures
 * @property GenericTextStructureAnnotation[] $genericTextStructureAnnotations
 * @package App\Model
 */
class Level extends AbstractModel
{
    /**
     * @return BelongsTo|Text
     * @throws ReflectionException
     */
    public function text(): belongsTo
    {
        return $this->belongsTo(Text::class);
    }

    public function agentiveRoles(): BelongsToMany
    {
        return $this->belongsToMany(AgentiveRole::class);
    }

    public function communicativeGoals(): BelongsToMany
    {
        return $this->belongsToMany(CommunicativeGoal::class);
    }

    public function productionStages(): BelongsToMany
    {
        return $this->belongsToMany(ProductionStage::class);
    }

    public function levelCategories(): BelongsToMany
    {
        return $this->belongsToMany(LevelCategory::class);
    }

    public function greekLatins(): BelongsToMany
    {
        return $this->belongsToMany(GreekLatin::class);
    }

    public function physicalObjects(): BelongsToMany
    {
        return $this->belongsToMany(PhysicalObject::class);
    }

    public function attestations(): HasMany
    {
        return $this->hasMany(Attestation::class);
    }

    public function genericTextStructures(): HasMany
    {
        return $this->hasMany(GenericTextStructure::class);
    }

    public function genericTextStructureAnnotations(): HasManyThrough
    {
        return $this->hasManyThrough(GenericTextStructureAnnotation::class, GenericTextStructure::class);
    }

}
