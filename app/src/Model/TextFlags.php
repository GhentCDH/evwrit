<?php

namespace App\Model;

use App\Model\AbstractModel;

/**
 * Class Script
 *
 * @property int $text_flags_id
 * @property int $text_id
 *
 * @property bool $needs_attention
 * @property bool $review_done
 *
 * @package App\Model
 */
class TextFlags extends AbstractModel
{
    protected $fillable = ['text_id', 'needs_attention', 'review_done'];

}
