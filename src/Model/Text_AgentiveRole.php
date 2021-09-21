<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Text_AgentiveRole
 *
 * @property int $text__agentive_role_id
 * @package App\Model
 */
class Text_AgentiveRole extends AbstractModel
{
    protected $table = "text__agentive_role";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    public function agentiveRole(): HasOne
    {
        return $this->hasOne(AgentiveRole::class, "agentive_role_id", "agentive_role_id");
    }

    public function genericAgentiveRole(): HasOne
    {
        return $this->hasOne(GenericAgentiveRole::class, "generic_agentive_role_id", "generic_agentive_role_id");
    }

}
