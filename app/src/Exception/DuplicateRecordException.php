<?php

namespace App\Exception;

class DuplicateRecordException extends \RuntimeException
{
    public function __construct(string $resource, string $name)
    {
        parent::__construct("A '{$resource}' record with name '{$name}' already exists.");
    }
}
