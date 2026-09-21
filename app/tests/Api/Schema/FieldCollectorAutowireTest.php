<?php

namespace App\Tests\Api\Schema;

use App\Api\Schema\Field\RelationField;
use App\Api\Schema\FieldCollector;
use App\Api\Schema\RelationIntrospector;
use App\Model\LexisAnnotation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Boots the kernel so relation introspection (BelongsTo) works.
 */
class FieldCollectorAutowireTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    private function collector(): FieldCollector
    {
        return new FieldCollector(LexisAnnotation::class, new RelationIntrospector());
    }

    /**
     * @return array<string, ?bool>
     */
    private function autowireById(FieldCollector $collector): array
    {
        $out = [];
        foreach ($collector->getFields() as $field) {
            if ($field instanceof RelationField) {
                $out[$field->getId()] = $field->getAutowire();
            }
        }

        return $out;
    }

    public function testDefaultOffUnlessEnabled(): void
    {
        $c = $this->collector();
        $c->relations('type', 'subtype');

        self::assertSame(['type' => false, 'subtype' => false], $this->autowireById($c));
    }

    public function testEnableAllViaDefault(): void
    {
        $c = $this->collector();
        $c->relations('type', 'subtype');
        $c->autowireResources(true);

        self::assertSame(['type' => true, 'subtype' => true], $this->autowireById($c));
    }

    public function testEnablePartialViaArrayIsOrderIndependent(): void
    {
        $c = $this->collector();
        $c->autowireResources(true, ['type']); // called before the relations exist
        $c->relations('type', 'subtype', 'wordclass');

        self::assertSame(['type' => true, 'subtype' => false, 'wordclass' => false], $this->autowireById($c));
    }

    public function testPerFieldOverrideWinsOverCollectorDefault(): void
    {
        $c = $this->collector();
        $c->autowireResources(true);
        $c->relation('type')->noResource();
        $c->relation('subtype');

        self::assertSame(['type' => false, 'subtype' => true], $this->autowireById($c));
    }
}
