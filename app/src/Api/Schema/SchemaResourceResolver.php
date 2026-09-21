<?php

namespace App\Api\Schema;

use Symfony\Component\Routing\RouterInterface;

/**
 * Resolves an autocomplete resource URL to the related model's schema-describe
 * endpoint, by looking the model up in the {@see SchemaRegistry}.
 */
class SchemaResourceResolver implements ResourceResolver
{
    public function __construct(
        private readonly SchemaRegistry $registry,
        private readonly RouterInterface $router,
    ) {
    }

    public function resolve(string $modelClass): ?string
    {
        $key = $this->registry->getKeyForModel($modelClass);
        if ($key === null) {
            return null;
        }

        return $this->router->generate('api_model_schema', ['key' => $key]);
    }
}
