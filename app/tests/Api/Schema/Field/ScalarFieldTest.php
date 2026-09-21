<?php

namespace App\Tests\Api\Schema\Field;

use App\Api\Schema\Field\ScalarField;
use App\Model\HandshiftAnnotation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ScalarFieldTest extends TestCase
{
    private function router(): RouterInterface
    {
        return $this->createMock(RouterInterface::class);
    }

    public function testStringFieldSchemaArray(): void
    {
        $field = (new ScalarField('status', ScalarField::TYPE_STRING))->colspan(6);
        $schema = $field->toSchemaArray($this->router());

        self::assertSame('status', $schema['id']);
        self::assertSame('Status', $schema['label']);
        self::assertSame(['type' => 'string'], $schema['type']);
        self::assertSame('text', $schema['fieldInput']['type']);
        self::assertSame(6, $schema['fieldInput']['options']['colspan']);
    }

    public function testDefaultWidgetsPerDataType(): void
    {
        self::assertSame('textarea', (new ScalarField('c', ScalarField::TYPE_TEXT))->toSchemaArray($this->router())['fieldInput']['type']);
        self::assertSame('number', (new ScalarField('n', ScalarField::TYPE_INTEGER))->toSchemaArray($this->router())['fieldInput']['type']);
        self::assertSame('boolean', (new ScalarField('b', ScalarField::TYPE_BOOLEAN))->toSchemaArray($this->router())['fieldInput']['type']);
        self::assertSame('date', (new ScalarField('d', ScalarField::TYPE_DATE))->toSchemaArray($this->router())['fieldInput']['type']);
        // long text is string data with a textarea widget
        self::assertSame('string', (new ScalarField('c', ScalarField::TYPE_TEXT))->toSchemaArray($this->router())['type']['type']);
    }

    public function testIntegerValueIsCastOnWrite(): void
    {
        $field = new ScalarField('internal_hand_num', ScalarField::TYPE_INTEGER);
        $model = new HandshiftAnnotation();

        $field->writeValue($model, '5');

        self::assertSame(5, $model->getAttribute('internal_hand_num'));
    }

    public function testNullIsPreservedOnWrite(): void
    {
        $field = new ScalarField('comment', ScalarField::TYPE_STRING);
        $model = new HandshiftAnnotation();

        $field->writeValue($model, null);

        self::assertNull($model->getAttribute('comment'));
    }

    public function testReadValueReturnsAttribute(): void
    {
        $field = new ScalarField('comment', ScalarField::TYPE_STRING);
        $model = new HandshiftAnnotation();
        $model->setAttribute('comment', 'hello');

        self::assertSame('hello', $field->readValue($model));
    }

    public function testRequiredFieldConstraintsAreNotNullWrapped(): void
    {
        $required = (new ScalarField('status', ScalarField::TYPE_STRING))->required();
        $optional = new ScalarField('status', ScalarField::TYPE_STRING);

        // required -> direct Type constraint; optional -> wrapped in AtLeastOneOf(IsNull, ...)
        self::assertInstanceOf(Assert\Type::class, $required->getConstraints()[0]);
        self::assertInstanceOf(Assert\AtLeastOneOf::class, $optional->getConstraints()[0]);
        self::assertTrue($required->isRequiredForCreate());
        self::assertFalse($optional->isRequiredForCreate());
    }

    public function testExposeAsMapsInternalColumnToExposedName(): void
    {
        $field = (new ScalarField('internal_hand_num', ScalarField::TYPE_INTEGER))->exposeAs('handNumber');

        self::assertSame('handNumber', $field->getId());
        self::assertSame('internal_hand_num', $field->getSource());
        self::assertSame('handNumber', $field->toSchemaArray($this->router())['id']);

        // read pulls from the internal column; write stores to the internal column
        $model = new HandshiftAnnotation();
        $model->setAttribute('internal_hand_num', 5);
        self::assertSame(5, $field->readValue($model));

        $target = new HandshiftAnnotation();
        $field->writeValue($target, 7);
        self::assertSame(7, $target->getAttribute('internal_hand_num'));
        self::assertArrayNotHasKey('handNumber', $target->getAttributes());
    }

    public function testReadonlyFieldIsNotWritable(): void
    {
        $field = (new ScalarField('status', ScalarField::TYPE_STRING))->readonly();

        self::assertFalse($field->isWritable());
    }
}
