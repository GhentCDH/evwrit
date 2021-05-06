<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use function Symfony\Component\String\u;

/**
 * Class Text
 *
 * @property int $text_id
 * @property string $title
 * @property string $text
 * @property int $year_begin
 * @property int $year_end
 * @package App\Model
 */
class Text extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['text_id','title','text'];


    public function era()
    {
        return $this->belongsTo( Era::class, 'era_id', 'era_id');
    }

    public function scripts()
    {
        return $this->belongsToMany(Script::class);
    }

    public function forms()
    {
        return $this->belongsToMany(Form::class);
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class);
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class);
    }

    public function socialDistances()
    {
        return $this->belongsToMany(SocialDistance::class);
    }

    public function productionStages()
    {
        return $this->belongsToMany(ProductionStage::class);
    }


}
