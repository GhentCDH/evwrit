<?php

namespace App\Tests\Api\Schema\Field;

use App\Api\Schema\Field\EmbeddedRelationField;
use App\Api\Schema\Field\ScalarField;
use App\Model\TextSelection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class EmbeddedRelationFieldTest extends TestCase
{
    private function field(string $mode = EmbeddedRelationField::MODE_FLATTENED): EmbeddedRelationField
    {
        return new EmbeddedRelationField(
            'textSelection',
            'text_selection_id',
            TextSelection::class,
            [
                new ScalarField('selection_start', ScalarField::TYPE_INTEGER),
                new ScalarField('text_id', ScalarField::TYPE_INTEGER),
            ],
            $mode,
        );
    }

    public function testLinkExposedByDefaultAndHideable(): void
    {
        self::assertTrue($this->field()->isLinkExposed());
        self::assertFalse($this->field()->hideLink()->isLinkExposed());
    }

    public function testExposedKeyRespectsPrefix(): void
    {
        $field = $this->field();
        self::assertSame('selection_start', $field->exposedKey('selection_start'));

        $field->prefix('textSelection:');
        self::assertSame('textSelection:selection_start', $field->exposedKey('selection_start'));
    }

    public function testSchemaArrayIsNestedObjectOfSubFields(): void
    {
        $schema = $this->field()->toSchemaArray($this->createMock(RouterInterface::class));

        self::assertSame('object', $schema['type']['type']);
        $props = $schema['type']['properties'];
        self::assertArrayHasKey('selection_start', $props);
        self::assertArrayHasKey('text_id', $props);
        // nested properties are JSON-schema-typed: `type` is a bare string, not {type:X}
        self::assertSame('integer', $props['selection_start']['type']);
        self::assertSame('integer', $props['text_id']['type']);
    }

    public function testExposeAsRenamesTheEmbed(): void
    {
        $field = $this->field()->exposeAs('selector');

        self::assertSame('selector', $field->getId());          // exposed alias
        self::assertSame('textSelection', $field->getSource()); // real relation method
    }

    public function testReadValueSerializesSubFieldsFromRelatedModel(): void
    {
        $field = $this->field();
        $parent = new TextSelection(); // any AbstractModel works as the "parent" holder
        $related = new TextSelection();
        $related->setAttribute('text_selection_id', 42);
        $related->setAttribute('selection_start', 5);
        $related->setAttribute('text_id', 7);
        $parent->setRelation('textSelection', $related);

        $value = $field->readValue($parent);

        self::assertSame(42, $value['id']);
        self::assertSame(5, $value['selection_start']);
        self::assertSame(7, $value['text_id']);
    }
}
