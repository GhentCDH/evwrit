<?php

namespace App\Exception;

class RecordNotFoundException extends \RuntimeException
{
    public function __construct(string $resource, string $id)
    {
        parent::__construct("No '{$resource}' record found with id '{$id}'.");
    }
}
