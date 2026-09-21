<?php

namespace App\Tests\Api\Schema;

use App\Api\Schema\RelationIntrospector;
use App\Model\HandshiftAnnotation;
use App\Model\Lookup\AnnotationScriptType;
use App\Model\TextSelection;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Boots the kernel so Eloquent's connection resolver is set (relations can be built).
 */
class RelationIntrospectorTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testInspectDerivesForeignKeyAndRelatedModel(): void
    {
        $meta = (new RelationIntrospector())->inspect(HandshiftAnnotation::class, 'scriptType');

        self::assertSame('annotation_script_type_id', $meta['foreignKey']);
        self::assertSame(AnnotationScriptType::class, $meta['relatedModel']);
        self::assertSame('name', $meta['labelAttribute']);
    }

    public function testInspectResolvesInheritedRelation(): void
    {
        $meta = (new RelationIntrospector())->inspect(HandshiftAnnotation::class, 'textSelection');

        self::assertSame('text_selection_id', $meta['foreignKey']);
        self::assertSame(TextSelection::class, $meta['relatedModel']);
    }

    public function testInspectUnknownMethodThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new RelationIntrospector())->inspect(HandshiftAnnotation::class, 'doesNotExist');
    }
}
