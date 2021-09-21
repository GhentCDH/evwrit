<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Text_CommunicativeGoal
 *
 * @property int $text__agentive_role_id
 * @package App\Model
 */
class Text_CommunicativeGoal extends AbstractModel
{
    protected $table = "text__communicative_goal";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    public function communicativeGoal(): HasOne
    {
        return $this->hasOne(CommunicativeGoal::class, "communicative_goal_id", "communicative_goal_id");
    }

    public function genericCommunicativeGoal(): HasOne
    {
        return $this->hasOne(GenericCommunicativeGoal::class, "generic_communicative_goal_id", "generic_communicative_goal_id");
    }

}
