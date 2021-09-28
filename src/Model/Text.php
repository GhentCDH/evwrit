<?php

namespace App\Model;

use App\Model\Lookup\Archive;
use App\Model\Lookup\Era;
use App\Model\Lookup\Form;
use App\Model\Lookup\Language;
use App\Model\Lookup\Material;
use App\Model\Lookup\ProductionStage;
use App\Model\Lookup\Script;
use App\Model\Lookup\SocialDistance;
use App\Model\Lookup\TextFormat;
use App\Model\Lookup\TextSubtype;
use App\Model\Lookup\TextType;
use App\Model\Lookup\WritingDirection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use ReflectionException;

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
class Text extends AbstractModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['text_id','title','text'];


    /**
     * @return BelongsTo|Era
     * @throws ReflectionException
     */
    public function era(): BelongsTo
    {
        return $this->belongsTo( Era::class, 'era_id', 'era_id');
    }

    /**
     * @return BelongsToMany|Collection|Script[]
     * @throws ReflectionException
     */
    public function scripts(): BelongsToMany
    {
        return $this->belongsToMany(Script::class);
    }

    /**
     * @return BelongsToMany|Collection|Form[]
     * @throws ReflectionException
     */
    public function forms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class);
    }

    /**
     * @return BelongsToMany|Collection|Language[]
     * @throws ReflectionException
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class);
    }

    /**
     * @return BelongsToMany|Collection|Material[]
     * @throws ReflectionException
     */
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class);
    }

    /**
     * @return BelongsToMany|Collection|SocialDistance[]
     * @throws ReflectionException
     */
    public function socialDistances(): BelongsToMany
    {
        return $this->belongsToMany(SocialDistance::class);
    }

    /**
     * @return BelongsToMany|Collection|ProductionStage[]
     * @throws ReflectionException
     */
    public function productionStages(): BelongsToMany
    {
        return $this->belongsToMany(ProductionStage::class);
    }

    /**
     * @return BelongsToMany|Collection|Project[]
     * @throws ReflectionException
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    /**
     * @return BelongsToMany|Collection|Keyword[]
     * @throws ReflectionException
     */
    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class);
    }

    /**
     * @return BelongsTo|Archive
     * @throws ReflectionException
     */
    public function archive(): BelongsTo
    {
        return $this->belongsTo( Archive::class, 'archive_id', 'archive_id');
    }

    /**
     * @return BelongsTo|TextType
     * @throws ReflectionException
     */
    public function textType(): BelongsTo
    {
        return $this->belongsTo( TextType::class, 'text_type_id', 'text_type_id');
    }

    /**
     * @return BelongsTo|TextSubtype
     * @throws ReflectionException
     */
    public function textSubtype(): BelongsTo
    {
        return $this->belongsTo( TextSubtype::class);
    }

    /**
     * @return BelongsTo|TextFormat
     * @throws ReflectionException
     */
    public function textFormat(): BelongsTo
    {
        return $this->belongsTo( TextFormat::class);
    }

    /**
     * @return BelongsToMany|Collection|Collaborator[]
     * @throws ReflectionException
     */
    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(Collaborator::class);
    }

    /**
     * @return BelongsToMany|Collection|Location[]
     * @throws ReflectionException
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class);
    }

    /**
     * @return BelongsToMany|Collection|Url[]
     * @throws ReflectionException
     */
    public function links(): BelongsToMany
    {
        return $this->belongsToMany(Url::class);
    }

    /**
     * @return BelongsToMany|Collection|Image[]
     * @throws ReflectionException
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class);
    }

    /**
     * @return BelongsToMany|Collection|WritingDirection[]
     * @throws ReflectionException
     */
    public function writingDirections(): BelongsToMany
    {
        return $this->belongsToMany(WritingDirection::class);
    }

    /**
     * @return HasMany|Attestation[]
     */
    public function attestations(): HasMany
    {
        return $this->hasMany(Attestation::class, 'text_id', 'text_id');
    }

    /**
     * @return HasMany|TextTranslation[]
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TextTranslation::class, 'text_id', 'text_id');
    }

    /**
     * @return BelongsToMany|Location[]
     * @throws ReflectionException
     */
    public function locationsFound()
    {
        //return Location::join('text__location','location.location_id','text__location.location_id')->select('location.*')->where('text__location.is_found',1)->where('text__location.text_id', $this->getId())->get();
        return $this->locations()->wherePivot('is_found', 1);
    }

    /**
     * @return BelongsToMany|Location[]
     * @throws ReflectionException
     */
    public function locationsWritten()
    {
        return $this->locations()->wherePivot('is_written',1);
    }

    /**
     * @return HasMany
     */
    public function textAgentiveRoles(): HasMany
    {
        return $this->hasMany(Text_AgentiveRole::class);
    }

    /**
     * @return HasMany
     */
    public function textCommunicativeGoals(): HasMany
    {
        return $this->hasMany(Text_CommunicativeGoal::class);
    }

    /**
     * @return HasMany|TextSelection[]
     */
    public function textSelections(): HasMany {
        return $this->hasMany(TextSelection::class);
    }

    /**
     * @return HasManyThrough|LexisAnnotation[]
     */
    public function lexisAnnotations()
    {
        return $this->hasManyThrough(LexisAnnotation::class, TextSelection::class);
    }

    /**
     * @return HasManyThrough|LanguageAnnotation[]
     */
    public function languageAnnotations()
    {
        return $this->hasManyThrough(LanguageAnnotation::class, TextSelection::class);
    }

    /**
     * @return HasManyThrough|TypographyAnnotation[]
     */
    public function typographyAnnotations()
    {
        return $this->hasManyThrough(TypographyAnnotation::class, TextSelection::class);
    }

    /**
     * @return HasManyThrough|OrthographyAnnotation[]
     */
    public function orthographyAnnotations()
    {
        return $this->hasManyThrough(OrthographyAnnotation::class, TextSelection::class);
    }

    /**
     * @return HasManyThrough|OrthographyAnnotation[]
     */
    public function morphologyAnnotations()
    {
        return $this->hasManyThrough(MorphologyAnnotation::class, TextSelection::class);
    }

}
