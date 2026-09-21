<?php

namespace App\Tests\Api\Service;

use App\Api\Exception\ValidationException;
use App\Api\Schema\SchemaInterface;
use App\Api\Schema\SchemaRegistry;
use App\Api\Service\ModelService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Exercises describe() and the schema-derived validation. Uses the real wired
 * services from the test container; none of these cases query the database.
 */
class ModelServiceTest extends KernelTestCase
{
    private ModelService $service;
    private SchemaInterface $schema;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->service = $container->get(ModelService::class);
        $this->schema = $container->get(SchemaRegistry::class)->get('handshift');
    }

    public function testDescribeExposesAllOperations(): void
    {
        $out = $this->service->describe($this->schema);

        self::assertSame('handshift', $out['id']);
        self::assertSame(
            [
                SchemaInterface::OP_FIND_ALL,
                SchemaInterface::OP_FIND_ONE,
                SchemaInterface::OP_CREATE,
                SchemaInterface::OP_UPDATE,
                SchemaInterface::OP_PATCH,
                SchemaInterface::OP_DELETE,
            ],
            array_keys($out['operations'])
        );
        self::assertSame('POST', $out['operations']['create']['method']);
        self::assertSame('/api/model/handshift/{id}', $out['operations']['findOne']['uri']);
        self::assertSame('#7a8800', $out['annotation']['color']);
    }

    public function testDescribeIncludesScalarsLookupsAndEmbedded(): void
    {
        $columns = $this->service->describe($this->schema)['columns'];
        $byId = [];
        foreach ($columns as $col) {
            $byId[$col['id']] = $col;
        }

        // 3 scalars + embedded textSelection + attestation + 14 lookups
        self::assertCount(19, $columns);
        self::assertSame('number', $byId['internal_hand_num']['fieldInput']['type']);
        self::assertSame('autocomplete', $byId['scriptType']['fieldInput']['type']);
        self::assertSame('object', $byId['scriptType']['type']['type']);

        // embedded selection is a nested object carrying its own columns, and the
        // object property itself has NO fieldInput (no invalid "fieldset")
        self::assertSame('object', $byId['textSelection']['type']['type']);
        self::assertArrayNotHasKey('fieldInput', $byId['textSelection']);
        $nested = $byId['textSelection']['type']['properties'];
        self::assertArrayHasKey('selection_start', $nested);
        self::assertArrayHasKey('sourceText', $nested);
        // nested props are full column configs with their own fieldInput
        self::assertSame('number', $nested['selection_start']['fieldInput']['type']);
    }

    public function testEveryEmittedWidgetIsInTheAllowedSet(): void
    {
        $allowed = ['text', 'textarea', 'number', 'boolean', 'date', 'select', 'autocomplete'];
        $columns = $this->service->describe($this->schema)['columns'];

        $collect = function (array $cols, callable $self): array {
            $types = [];
            foreach ($cols as $col) {
                if (!is_array($col)) {
                    continue;
                }
                if (isset($col['fieldInput']['type'])) {
                    $types[] = $col['fieldInput']['type'];
                }
                if (isset($col['type']['properties']) && is_array($col['type']['properties'])) {
                    $types = array_merge($types, $self($col['type']['properties'], $self));
                }
            }

            return $types;
        };

        foreach ($collect($columns, $collect) as $type) {
            self::assertContains($type, $allowed, "Unexpected widget type: {$type}");
        }
    }

    public function testValidateReportsMissingRequiredEmbedded(): void
    {
        try {
            $this->service->validate($this->schema, [], SchemaInterface::OP_CREATE);
            self::fail('Expected ValidationException');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('textSelection', $e->getErrors());
        }
    }

    public function testValidateReportsWrongScalarType(): void
    {
        try {
            $this->service->validate(
                $this->schema,
                ['internal_hand_num' => 'not-an-int'],
                SchemaInterface::OP_CREATE
            );
            self::fail('Expected ValidationException');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('internal_hand_num', $e->getErrors());
        }
    }

    public function testValidatePatchWithSingleFieldPasses(): void
    {
        $this->expectNotToPerformAssertions();
        $this->service->validate($this->schema, ['comment' => 'hi'], SchemaInterface::OP_PATCH);
    }
}
