<?php

namespace App\Service\ElasticSearch\Base;

use App\Resource\BaseResource;
use Elastica\Document;
use Elastica\Mapping;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Ramsey\Uuid\Uuid;

abstract class AbstractIndexService extends AbstractService implements IndexServiceInterface
{
    abstract protected function getIndexProperties(): array;

    abstract protected function getMappingProperties(): array;

    public function setup(): void
    {
        $index = $this->getIndex();

        // delete index
        if ($index->exists()) {
            $index->delete();
        }

        // configure analysis
        $index->create($this->getIndexProperties());

        // configure mapping
        $mapProperties = $this->getMappingProperties();
        if (count($mapProperties)) {
            $mapping = new Mapping;
            $mapping->setProperties($mapProperties);
            $mapping->send($this->getIndex());
        }
    }

    public function createNewIndex(): string
    {
        $newIndexName = $this->getIndexName().'_'.Uuid::uuid4()->toString();

        // create index
        $index = $this->client->getIndex($newIndexName);
        $index->create($this->getIndexProperties());

        // configure mapping
        $mapProperties = $this->getMappingProperties();
        if (count($mapProperties)) {
            $mapping = new Mapping;
            $mapping->setProperties($mapProperties);
            $mapping->send($index);
        }

        $this->index = $index;

        return $newIndexName;
    }

    public function switchToNewIndex(string $newIndexName)
    {
        $oldIndices = $this->client->getStatus()->getIndicesWithAlias($this->getIndexName());
        foreach ($oldIndices as $oldIndex) {
            $this->removeIndex($oldIndex->getName());
        }

        $this->client->getIndex($newIndexName)->addAlias($this->getIndexName());
    }

    public function addMultiple(ResourceCollection $resources): void
    {
        /*
        $json_array = $resources->toJson();

        $bulk_documents = [];
        while (count($elastics) > 0) {
            $bulk_contents = array_splice($elastics, 0, 500);
            foreach ($bulk_contents as $bc) {
                $bulk_documents[] = new Document($resource->getId(), $resource->getJson());
            }
            $this->getIndex()->addDocuments($bulk_documents);
            $bulk_documents = [];
        }
        $this->getIndex()->refresh();
        */
        $documents = [];
        foreach( $resources as $resource ) {
            $documents[] = new Document($resource->getId(), $resource->toJson());
        }
        $this->getIndex()->addDocuments($documents);
        $this->getIndex()->refresh();
    }

    public function add(BaseResource $resource): void
    {
        $id = $resource->getId();
        $json = $resource->toJson();

        $document = new Document($id, $json);
        $this->getIndex()->addDocument($document);
        $this->getIndex()->refresh();
    }

    public function deleteMultiple(array $ids): void
    {
        $this->getClient()->deleteIds($ids, $this->getIndex());
        $this->getIndex()->refresh();
    }

    public function delete(int $id): void
    {
        $this->getIndex()->deleteById($id);
        $this->getIndex()->refresh();
    }

    public function get(int $id) {
        $ret = $this->getIndex()->getDocument($id)->getData();
        return $ret;
    }

    /*
    public function updateMultiple(array $data): void
    {
        $bulk_documents = [];
        while (count($data) > 0) {
            $bulk_contents = array_splice($data, 0, 500);
            foreach ($bulk_contents as $bc) {
                $bulk_documents[] = new Document($bc['id'], $bc);
            }
            $this->getIndex()->updateDocuments($bulk_documents);
            $bulk_documents = [];
        }
        $this->getIndex()->refresh();
    }
    */
}