<?php

namespace App\Tests\Api\Service;

use App\Api\Schema\SchemaRegistry;
use App\Api\Service\ModelService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * GET /api/model directory: annotation services listed, lookups hidden by default.
 */
class ServiceDirectoryTest extends KernelTestCase
{
    private ModelService $service;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = self::getContainer()->get(ModelService::class);
    }

    public function testListsAnnotationServicesAndExcludesLookups(): void
    {
        $ids = array_column($this->service->listServices(), 'id');

        self::assertContains('lexis_annotation', $ids);
        self::assertContains('handshift_annotation', $ids);
        self::assertNotContains('annotation_type_lexis', $ids); // lookup hidden by default
    }

    public function testEntryShape(): void
    {
        $entry = null;
        foreach ($this->service->listServices() as $row) {
            if ($row['id'] === 'lexis_annotation') {
                $entry = $row;
            }
        }

        self::assertNotNull($entry);
        self::assertSame(['id', 'label', 'uri'], array_keys($entry));
        self::assertNotEmpty($entry['label']);
        self::assertSame('/api/model/lexis_annotation/schema', $entry['uri']);
    }

    public function testAllIncludesLookups(): void
    {
        $ids = array_column($this->service->listServices(true), 'id');

        self::assertContains('annotation_type_lexis', $ids);
    }

    public function testListedFlags(): void
    {
        $registry = self::getContainer()->get(SchemaRegistry::class);

        self::assertTrue($registry->get('lexis_annotation')->isListed());          // annotation -> listed
        self::assertFalse($registry->get('annotation_type_lexis')->isListed()); // lookup -> hidden
    }
}
