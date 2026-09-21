<?php

namespace App\Api\Schema;

/**
 * Resolves the "resource" URL an autocomplete field points at, given the related
 * model class. Returns null when nothing exposes that model.
 */
interface ResourceResolver
{
    /**
     * @param class-string $modelClass
     */
    public function resolve(string $modelClass): ?string;
}
