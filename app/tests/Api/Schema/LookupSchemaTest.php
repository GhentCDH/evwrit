<?php

namespace App\Tests\Api\Schema;

use App\Api\Schema\SchemaInterface;
use App\Api\Schema\SchemaRegistry;
use App\Api\Service\ModelService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * A lookup schema exposes the primary key as a read-only `id` column plus `name`.
 */
class LookupSchemaTest extends KernelTestCase
{
    private ModelService $service;
    private SchemaInterface $schema;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->service = $container->get(ModelService::class);
        $this->schema = $container->get(SchemaRegistry::class)->get('annotation_type_lexis');
    }

    public function testDescribeExposesIdAndName(): void
    {
        $columns = $this->service->describe($this->schema)['columns'];

        self::assertArrayHasKey('id', $columns);
        self::assertArrayHasKey('name', $columns);

        self::assertSame('integer', $columns['id']['type']['type']);
        self::assertFalse($columns['id']['creatable']);
        self::assertFalse($columns['id']['updatable']);
    }

    public function testPrimaryKeyIsNotWrittenOnCreateOrUpdate(): void
    {
        $idField = null;
        foreach ($this->schema->getFields() as $field) {
            if ($field->getId() === 'id') {
                $idField = $field;
            }
        }

        self::assertNotNull($idField);
        self::assertFalse($idField->isWritableForOperation(SchemaInterface::OP_CREATE));
        self::assertFalse($idField->isWritableForOperation(SchemaInterface::OP_UPDATE));
        self::assertFalse($idField->isWritableForOperation(SchemaInterface::OP_PATCH));
        // its source stays the real PK column, not the exposed "id"
        self::assertSame('annotation_type_lexis_id', $idField->getSource());
    }
}
