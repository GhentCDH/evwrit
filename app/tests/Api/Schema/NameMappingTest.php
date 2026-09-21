<?php

namespace App\Tests\Api\Schema;

use App\Api\Schema\AbstractSchema;
use App\Api\Schema\Field\EmbeddedRelationField;
use App\Api\Schema\FieldCollector;
use App\Api\Service\ModelService;
use App\Model\HandshiftAnnotation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * exposeAs() must map internal names to exposed names across describe / eager loading,
 * while eager loading still uses the real relation methods.
 */
class NameMappingTest extends KernelTestCase
{
    private function schema(): AbstractSchema
    {
        return new class extends AbstractSchema {
            protected function configure(): void
            {
                $this->key('t')->name('T')->model(HandshiftAnnotation::class)
                    ->allow(self::OP_FIND_ONE, self::OP_CREATE);
                $this->integer('internal_hand_num')->exposeAs('handNumber');
                $this->relation('scriptType')->exposeAs('script');
                $this->embed('textSelection', function (FieldCollector $f): void {
                    $f->integer('selection_start')->exposeAs('start');
                }, EmbeddedRelationField::MODE_NESTED)->exposeAs('selection');
            }
        };
    }

    public function testDescribeUsesExposedNames(): void
    {
        self::bootKernel();
        $service = self::getContainer()->get(ModelService::class);

        $columns = $service->describe($this->schema())['columns'];

        self::assertSame(['handNumber', 'script', 'selection'], array_keys($columns));
        self::assertSame(['start'], array_keys($columns['selection']['type']['properties']));
    }

    public function testEagerRelationsUseSourceNames(): void
    {
        self::bootKernel();

        // real relation methods, not the exposed aliases ("script"/"selection")
        self::assertSame(['scriptType', 'textSelection'], $this->schema()->getEagerRelations());
    }
}
