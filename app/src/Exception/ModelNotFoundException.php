<?php

namespace App\Exception;

class ModelNotFoundException extends \RuntimeException
{
    public function __construct(string $modelName)
    {
        parent::__construct("Unknown model: '{$modelName}'.");
    }
}
