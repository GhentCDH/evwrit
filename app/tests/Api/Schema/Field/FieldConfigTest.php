<?php

namespace App\Tests\Api\Schema\Field;

use App\Api\Schema\Field\RelationField;
use App\Api\Schema\Field\ScalarField;
use App\Api\Schema\SchemaInterface;
use App\Model\Lookup\AnnotationScriptType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class FieldConfigTest extends TestCase
{
    private function router(): RouterInterface
    {
        return $this->createMock(RouterInterface::class);
    }

    public function testFlagsAreOmittedWhenUnset(): void
    {
        $schema = (new ScalarField('name', ScalarField::TYPE_STRING))->toSchemaArray($this->router());

        foreach (['hiddenInTable', 'hiddenInForm', 'hiddenInView', 'creatable', 'updatable'] as $key) {
            self::assertArrayNotHasKey($key, $schema);
        }
    }

    public function testFlagsAreEmittedWhenSet(): void
    {
        $schema = (new ScalarField('name', ScalarField::TYPE_STRING))
            ->hiddenInTable()
            ->creatable(true)
            ->updatable(false)
            ->toSchemaArray($this->router());

        self::assertTrue($schema['hiddenInTable']);
        self::assertTrue($schema['creatable']);
        self::assertFalse($schema['updatable']);
        self::assertArrayNotHasKey('hiddenInForm', $schema);
    }

    public function testHiddenSetsAllThreeVisibilityFlags(): void
    {
        $schema = (new ScalarField('x', ScalarField::TYPE_INTEGER))->hidden()->toSchemaArray($this->router());

        self::assertTrue($schema['hiddenInTable']);
        self::assertTrue($schema['hiddenInForm']);
        self::assertTrue($schema['hiddenInView']);
        // hidden is NOT a widget type
        self::assertSame('number', $schema['fieldInput']['type']);
    }

    public function testCreatableUpdatableGateWritability(): void
    {
        $notCreatable = (new ScalarField('x', ScalarField::TYPE_STRING))->creatable(false);
        self::assertFalse($notCreatable->isWritableForOperation(SchemaInterface::OP_CREATE));
        self::assertTrue($notCreatable->isWritableForOperation(SchemaInterface::OP_PATCH));

        $notUpdatable = (new ScalarField('x', ScalarField::TYPE_STRING))->updatable(false);
        self::assertTrue($notUpdatable->isWritableForOperation(SchemaInterface::OP_CREATE));
        self::assertFalse($notUpdatable->isWritableForOperation(SchemaInterface::OP_UPDATE));
        self::assertFalse($notUpdatable->isWritableForOperation(SchemaInterface::OP_PATCH));

        $readonly = (new ScalarField('x', ScalarField::TYPE_STRING))->readonly();
        self::assertFalse($readonly->isWritableForOperation(SchemaInterface::OP_CREATE));
        self::assertFalse($readonly->isWritableForOperation(SchemaInterface::OP_PATCH));

        $plain = new ScalarField('x', ScalarField::TYPE_STRING);
        self::assertTrue($plain->isWritableForOperation(SchemaInterface::OP_CREATE));
    }

    public function testCustomAttributesAndPositionAndFieldInputOptions(): void
    {
        $schema = (new ScalarField('gender', ScalarField::TYPE_STRING, null, 'select'))
            ->attr('foo', 'bar')
            ->position(3)
            ->fieldInputOptions(['values' => [['value' => 'm', 'label' => 'Male']], 'clearable' => false])
            ->toSchemaArray($this->router());

        self::assertSame('bar', $schema['foo']);
        self::assertSame(3, $schema['fieldInput']['position']);
        self::assertSame('select', $schema['fieldInput']['type']);
        self::assertSame([['value' => 'm', 'label' => 'Male']], $schema['fieldInput']['options']['values']);
        self::assertFalse($schema['fieldInput']['options']['clearable']);
    }

    public function testNoFieldInputOmitsFieldInput(): void
    {
        $with = (new ScalarField('x', ScalarField::TYPE_INTEGER))->toSchemaArray($this->router());
        self::assertArrayHasKey('fieldInput', $with);

        $without = (new ScalarField('x', ScalarField::TYPE_INTEGER))->noFieldInput()->toSchemaArray($this->router());
        self::assertArrayNotHasKey('fieldInput', $without);
        // other keys remain
        self::assertSame('x', $without['id']);
        self::assertSame(['type' => 'integer'], $without['type']);

        $relation = (new RelationField('scriptType', 'annotation_script_type_id', AnnotationScriptType::class))
            ->noFieldInput()
            ->toSchemaArray($this->router());
        self::assertArrayNotHasKey('fieldInput', $relation);
    }

    public function testRelationFieldSupportsFlags(): void
    {
        $schema = (new RelationField('scriptType', 'annotation_script_type_id', AnnotationScriptType::class))
            ->updatable(false)
            ->toSchemaArray($this->router());

        self::assertFalse($schema['updatable']);
        self::assertSame('autocomplete', $schema['fieldInput']['type']);
    }
}
