<?php

namespace App\Api\Schema\Model;

use App\Api\Schema\AbstractSchema;
use App\Model\Text;

/**
 * Read-only schema for {@see Text}: fetch a text's content by id (findOne only).
 */
class TextSchema extends AbstractSchema
{
    protected function configure(): void
    {
        $this->key('text')
            ->name('Text')
            ->model(Text::class)
            ->allow(self::OP_FIND_ONE);

        $this->primaryKey('id'); // PK exposed as text_id (read-only, no fieldInput)
        $this->text('text');          // content column
    }
}
