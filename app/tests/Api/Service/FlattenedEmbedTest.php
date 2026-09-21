<?php

namespace App\Tests\Api\Service;

use App\Api\Exception\ValidationException;
use App\Api\Schema\AbstractSchema;
use App\Api\Schema\Field\EmbeddedRelationField;
use App\Api\Schema\FieldCollector;
use App\Api\Schema\SchemaInterface;
use App\Api\Service\ModelService;
use App\Model\HandshiftAnnotation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Flattened + link-hidden embedded relation, against a stable fixture schema (so it
 * does not depend on a reference schema's current embed mode). No DB queries.
 */
class FlattenedEmbedTest extends KernelTestCase
{
    private ModelService $service;
    private AbstractSchema $schema;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = self::getContainer()->get(ModelService::class);
        $this->schema = new class extends AbstractSchema {
            protected function configure(): void
            {
                $this->key('fixture')->name('Fixture')->model(HandshiftAnnotation::class)
                    ->allow(self::OP_CREATE, self::OP_PATCH);
                $this->embed('textSelection', function (FieldCollector $f): void {
                    $f->integer('text_id')->hidden()->required()->noFieldInput();
                    $f->integer('selection_start')->required();
                    $f->text('text_edited')->required();
                }, EmbeddedRelationField::MODE_FLATTENED)->hideLink();
            }
        };
    }

    public function testDescribeIsFlatAndHidesTheLink(): void
    {
        $columns = $this->service->describe($this->schema)['columns'];

        self::assertArrayNotHasKey('text_selection_id', $columns);
        self::assertArrayHasKey('text_id', $columns);
        self::assertTrue($columns['text_id']['hiddenInForm']);
        // text_id is set by another process, not a form -> no fieldInput
        self::assertArrayNotHasKey('fieldInput', $columns['text_id']);
        self::assertArrayHasKey('fieldInput', $columns['selection_start']);
        // flattened child columns are promoted to the top level
        self::assertArrayHasKey('selection_start', $columns);
        self::assertArrayHasKey('text_edited', $columns);
    }

    public function testCreateRequiresFlattenedChildFields(): void
    {
        try {
            $this->service->validate($this->schema, [], SchemaInterface::OP_CREATE);
            self::fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            self::assertArrayHasKey('text_id', $errors);
            self::assertArrayHasKey('selection_start', $errors);
            self::assertArrayNotHasKey('text_selection_id', $errors);
        }
    }

    public function testPatchSingleFlattenedFieldPasses(): void
    {
        // A hidden-link partial PATCH must not demand the other required child fields.
        $this->expectNotToPerformAssertions();
        $this->service->validate($this->schema, ['selection_start' => 3], SchemaInterface::OP_PATCH);
    }
}
