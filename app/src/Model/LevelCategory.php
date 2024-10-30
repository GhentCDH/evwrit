<?php

namespace App\Model;


use App\Model\Lookup\LevelCategoryCategory;
use App\Model\Lookup\LevelCategoryHypercategory;
use App\Model\Lookup\LevelCategorySubcategory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionException;

/**
 * Class TextTranslation
 *
 * @property int $level_category_id
 * @property string category
 * @property string subcategory
 * @property string hypercategory
 * @property LevelCategoryCategory levelCategory
 * @property LevelCategorySubcategory levelSubcategory
 * @property LevelCategoryHypercategory levelHypercategory
 * @package App\Model
 */
class LevelCategory extends AbstractModel
{
    public function levelCategory(): BelongsTo
    {
        return $this->belongsTo(LevelCategoryCategory::class);
    }

    public function levelSubcategory(): BelongsTo
    {
        return $this->belongsTo(LevelCategorySubcategory::class);
    }

    public function levelHypercategory(): BelongsTo
    {
        return $this->belongsTo(LevelCategoryHypercategory::class);
    }
}
