<?php

namespace App\Tests\Api\Service;

use App\Api\Schema\SchemaRegistry;
use App\Api\Service\ModelService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Lexis-specific: its lookup relations autowire their resource from the registry.
 * (Flattened-embed behaviour is covered by FlattenedEmbedTest with a stable fixture,
 * so it does not depend on the lexis schema's current embed mode.)
 */
class ModelServiceLexisTest extends KernelTestCase
{
    public function testLookupResourceIsAutowiredWhereASchemaExists(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $schema = $container->get(SchemaRegistry::class)->get('lexis_annotation');

        $columns = $container->get(ModelService::class)->describe($schema)['columns'];

        // registered lookup schemas -> resource auto-filled to their /schema endpoint
        self::assertSame(
            '/api/model/annotation_type_lexis/schema',
            $columns['type']['fieldInput']['options']['resource'] ?? null
        );
        self::assertSame(
            '/api/model/annotation_standard_form_lexis/schema',
            $columns['standardForm']['fieldInput']['options']['resource'] ?? null
        );
    }
}
