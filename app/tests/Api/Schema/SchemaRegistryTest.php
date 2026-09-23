<?php

namespace App\Tests\Api\Schema;

use App\Api\Schema\SchemaInterface;
use App\Api\Schema\SchemaRegistry;
use App\Model\AbstractModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SchemaRegistryTest extends TestCase
{
    private function schema(string $key, string $modelClass = AbstractModel::class): SchemaInterface
    {
        return new class($key, $modelClass) implements SchemaInterface {
            public function __construct(private readonly string $key, private readonly string $modelClass)
            {
            }

            public function getKey(): string
            {
                return $this->key;
            }

            public function getName(): string
            {
                return ucfirst($this->key);
            }

            public function getModelClass(): string
            {
                return $this->modelClass;
            }

            public function getFields(): array
            {
                return [];
            }

            public function getAllowedOperations(): array
            {
                return [];
            }

            public function getMetadata(): array
            {
                return [];
            }

            public function getEagerRelations(): array
            {
                return [];
            }

            public function getWriteHooks(): array
            {
                return [];
            }

            public function applyListFilters(\Illuminate\Database\Eloquent\Builder $query, array $filters): void
            {
            }

            public function getKind(): string
            {
                return 'custom';
            }

            public function getExtra(): array
            {
                return [];
            }

            public function isListed(): bool
            {
                return true;
            }
        };
    }

    public function testResolvesSchemaByKey(): void
    {
        $registry = new SchemaRegistry([$this->schema('handshift'), $this->schema('lemma')]);

        self::assertTrue($registry->has('handshift'));
        self::assertSame('handshift', $registry->get('handshift')->getKey());
        self::assertCount(2, $registry->all());
    }

    public function testUnknownKeyThrowsNotFound(): void
    {
        $registry = new SchemaRegistry([$this->schema('handshift')]);

        self::assertFalse($registry->has('bogus'));
        $this->expectException(NotFoundHttpException::class);
        $registry->get('bogus');
    }

    public function testReverseLookupByModel(): void
    {
        $registry = new SchemaRegistry([
            $this->schema('handshift', \App\Model\HandshiftAnnotation::class),
            $this->schema('lexis', \App\Model\LexisAnnotation::class),
        ]);

        self::assertTrue($registry->hasModel(\App\Model\LexisAnnotation::class));
        self::assertSame('lexis', $registry->getKeyForModel(\App\Model\LexisAnnotation::class));
        self::assertSame('handshift', $registry->getKeyForModel(\App\Model\HandshiftAnnotation::class));

        self::assertFalse($registry->hasModel(\App\Model\TextSelection::class));
        self::assertNull($registry->getKeyForModel(\App\Model\TextSelection::class));
    }
}
