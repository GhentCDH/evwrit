<?php

namespace App\Tests\Api\Schema\Field;

use App\Api\Schema\Field\RelationField;
use App\Api\Schema\FieldCollector;
use App\Api\Schema\ResourceResolver;
use App\Api\Validator\FkExists;
use App\Model\HandshiftAnnotation;
use App\Model\Lookup\AnnotationScriptType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class RelationFieldTest extends TestCase
{
    private function field(): RelationField
    {
        return new RelationField(
            'scriptType',
            'annotation_script_type_id',
            AnnotationScriptType::class,
        );
    }

    private function router(): RouterInterface
    {
        return $this->createMock(RouterInterface::class);
    }

    public function testExtractIdFromObjectBareIdAndNull(): void
    {
        $field = $this->field();

        self::assertSame(5, $field->extractId(['id' => 5, 'label' => 'ignored']));
        self::assertSame(7, $field->extractId(7));
        self::assertNull($field->extractId(null));
        self::assertNull($field->extractId(['label' => 'no id']));
    }

    public function testSchemaArrayDescribesIdLabelObject(): void
    {
        $schema = $this->field()->lookup('/api/model/script_type')->toSchemaArray($this->router());

        self::assertSame('object', $schema['type']['type']);
        self::assertArrayHasKey('id', $schema['type']['properties']);
        self::assertArrayHasKey('label', $schema['type']['properties']);
        self::assertSame('autocomplete', $schema['fieldInput']['type']);
        self::assertSame('id', $schema['fieldInput']['options']['valueKey']);
        self::assertSame('label', $schema['fieldInput']['options']['labelKey']);
        self::assertSame('/api/model/script_type', $schema['fieldInput']['options']['resource']);
    }

    public function testWriteValueStoresOnlyForeignKeyId(): void
    {
        $model = new HandshiftAnnotation();

        $this->field()->writeValue($model, ['id' => 3, 'label' => 'Cursive']);

        self::assertSame(3, $model->getAttribute('annotation_script_type_id'));
        // the label must not leak onto the model
        self::assertArrayNotHasKey('label', $model->getAttributes());
    }

    private function resolver(?string $url): ResourceResolver
    {
        return new class($url) implements ResourceResolver {
            public function __construct(private readonly ?string $url)
            {
            }

            public function resolve(string $modelClass): ?string
            {
                return $this->url;
            }
        };
    }

    public function testAutowireFillsResourceFromResolver(): void
    {
        $schema = $this->field()
            ->autowireResource()
            ->toSchemaArray($this->router(), $this->resolver('/api/model/annotation_script_type/schema'));

        self::assertSame('/api/model/annotation_script_type/schema', $schema['fieldInput']['options']['resource']);
    }

    public function testAutowireWithoutMatchingSchemaOmitsResource(): void
    {
        // autowire on, but the resolver finds no schema for the related model
        $schema = $this->field()
            ->autowireResource()
            ->toSchemaArray($this->router(), $this->resolver(null));

        self::assertArrayNotHasKey('resource', $schema['fieldInput']['options']);
    }

    public function testAutowireDisabledOmitsResource(): void
    {
        $schema = $this->field()
            ->noResource()
            ->toSchemaArray($this->router(), $this->resolver('/should/not/appear'));

        self::assertArrayNotHasKey('resource', $schema['fieldInput']['options']);
    }

    public function testExplicitLookupOverridesAutowire(): void
    {
        $schema = $this->field()
            ->autowireResource()
            ->lookup('/custom/resource')
            ->toSchemaArray($this->router(), $this->resolver('/autowired'));

        self::assertSame('/custom/resource', $schema['fieldInput']['options']['resource']);
    }

    public function testObjectProjectionAddsPropertiesToSchema(): void
    {
        $schema = $this->field()
            ->object(fn (FieldCollector $f) => $f->string('name'))
            ->toSchemaArray($this->router());

        $props = $schema['type']['properties'];
        self::assertArrayHasKey('id', $props);
        self::assertArrayHasKey('label', $props);
        // projected properties are JSON-schema-typed (bare `type`), keep their fieldInput
        self::assertSame('string', $props['name']['type']);
        self::assertSame('text', $props['name']['fieldInput']['type']);
    }

    public function testObjectProjectionIsReadOnly(): void
    {
        $field = $this->field()->object(fn (FieldCollector $f) => $f->string('name'));

        // read includes the projected property
        $parent = new HandshiftAnnotation();
        $related = new AnnotationScriptType();
        $related->setAttribute('annotation_script_type_id', 5);
        $related->setAttribute('name', 'cursive');
        $parent->setRelation('scriptType', $related);

        $value = $field->readValue($parent);
        self::assertSame(5, $value['id']);
        self::assertSame('cursive', $value['name']);

        // write stores only the id; projected props are ignored
        $target = new HandshiftAnnotation();
        $field->writeValue($target, ['id' => 9, 'name' => 'ignored']);
        self::assertSame(9, $target->getAttribute('annotation_script_type_id'));
        self::assertArrayNotHasKey('name', $target->getAttributes());
    }

    public function testExposeAsMapsRelationToExposedName(): void
    {
        $field = $this->field()->exposeAs('script');

        self::assertSame('script', $field->getId());
        self::assertSame('scriptType', $field->getSource()); // relation method unchanged
        self::assertSame('script', $field->toSchemaArray($this->router())['id']);

        // write still stores the foreign-key column
        $model = new HandshiftAnnotation();
        $field->writeValue($model, ['id' => 4, 'label' => 'x']);
        self::assertSame(4, $model->getAttribute('annotation_script_type_id'));
    }

    public function testConstraintsIncludeFkExistsWhenRequired(): void
    {
        $constraints = (new RelationField('scriptType', 'annotation_script_type_id', AnnotationScriptType::class))
            ->required()
            ->getConstraints();

        $hasFkExists = false;
        foreach ($constraints as $constraint) {
            if ($constraint instanceof FkExists) {
                $hasFkExists = true;
                self::assertSame(AnnotationScriptType::class, $constraint->modelClass);
            }
        }
        self::assertTrue($hasFkExists, 'Expected an FkExists constraint');
    }
}
