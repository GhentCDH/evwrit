<?php

namespace App\Service\Lookup;

class LookupUriTranslator
{
    public function __construct(private readonly string $modelName) {}

    public function toUri(int $id): string
    {
        return "evrwit:{$this->modelName}:{$id}";
    }

    public function fromUri(string $uri): ?int
    {
        $pattern = "/^evrwit:{$this->modelName}:(\d+)$/";
        if (preg_match($pattern, $uri, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }
}
