<?php

namespace App\Model;


use App\Model\Lookup\CommunicativeGoalSubtype;
use App\Model\Lookup\CommunicativeGoalType;
use App\Model\Lookup\GenericAgentiveRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CommunicativeGoal
 * @property int communicative_goal_id
 * @property string type
 * @property string subtype
 *
 * @property CommunicativeGoalType $communicativeGoalType
 * @property CommunicativeGoalSubtype $communicativeGoalSubtype
 * @package App\Model
 */
class CommunicativeGoal extends AbstractModel
{
    public function communicativeGoalType(): BelongsTo
    {
        return $this->belongsTo(CommunicativeGoalType::class);
    }

    public function communicativeGoalSubtype(): BelongsTo
    {
        return $this->belongsTo(CommunicativeGoalSubtype::class);
    }
}
