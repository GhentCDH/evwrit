<?php

namespace App\Model;


use App\Model\Lookup\GenericAgentiveRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AgentiveRole
 * @property int agentive_role_id
 * @property int generic_agentive_role_id
 * @property string name
 * @property string generic_name
 * @package App\Model
 */
class AgentiveRole extends AbstractModel implements IdNameModelInterface
{
    public function getName(): string {
        return $this->name;
    }

    public function genericAgentiveRole(): BelongsTo
    {
        return $this->belongsTo(GenericAgentiveRole::class);
    }
}
