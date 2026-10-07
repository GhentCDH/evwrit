<?php

namespace App\Tests\Api\Schema;

use App\Api\Schema\Field\EmbeddedRelationField;
use App\Api\Schema\Field\ScalarField;
use App\Api\Schema\SchemaRegistry;
use Illuminate\Database\Capsule\Manager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Guards against schema fields that reference a DB column that doesn't exist (e.g. a field
 * named `updated` while the column is `modified`). Such a mismatch is invisible to
 * describe()/validation and silently reads back as null, only failing on a write — so it
 * must be caught structurally, across every registered schema.
 */
class SchemaColumnsTest extends KernelTestCase
{
    public function testEverySchemaScalarFieldMapsToARealColumn(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $registry = $container->get(SchemaRegistry::class);
        $builder = $container->get(Manager::class)->getConnection()->getSchemaBuilder();

        /** @var array<string, string[]> $cache table => columns */
        $cache = [];
        $columnsOf = static function (string $modelClass) use (&$cache, $builder): array {
            $table = (new $modelClass())->getTable();

            return $cache[$table] ??= $builder->getColumnListing($table);
        };

        $problems = [];
        foreach ($registry->all() as $schema) {
            $modelClass = $schema->getModelClass();
            $columns = $columnsOf($modelClass);

            foreach ($schema->getFields() as $field) {
                if ($field instanceof ScalarField) {
                    if (!in_array($field->getSource(), $columns, true)) {
                        $problems[] = sprintf(
                            '%s.%s -> no column "%s" on %s',
                            $schema->getKey(),
                            $field->getId(),
                            $field->getSource(),
                            (new $modelClass())->getTable()
                        );
                    }
                    continue;
                }

                if ($field instanceof EmbeddedRelationField) {
                    // The related table, via the belongsTo method (builds the relation, no query).
                    $related = (new $modelClass())->{$field->getSource()}()->getRelated();
                    $subColumns = $columnsOf($related::class);
                    foreach ($field->getSubFields() as $sub) {
                        if ($sub instanceof ScalarField && !in_array($sub->getSource(), $subColumns, true)) {
                            $problems[] = sprintf(
                                '%s.%s.%s -> no column "%s" on %s',
                                $schema->getKey(),
                                $field->getId(),
                                $sub->getId(),
                                $sub->getSource(),
                                $related->getTable()
                            );
                        }
                    }
                }
            }
        }

        self::assertSame(
            [],
            $problems,
            "Schema fields reference DB columns that don't exist:\n".implode("\n", $problems)
        );
    }
}
