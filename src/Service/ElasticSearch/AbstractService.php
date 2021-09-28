<?php

namespace App\Service\ElasticSearch;

use Elastica\Index;

abstract class AbstractService
{
    protected $client;
    protected $indexName;
    protected $index;

    protected function __construct(
        Client $client,
        string $indexName
    ) {
        $this->client = $client;
        $this->indexName = $indexName;
        $this->index = $this->client->getIndex($indexName);
    }

    /**
     * Return Elasticsearch Client
     *
     * @return Index
     */
    protected function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Return Elasticsearch Index
     *
     * @return Index
     */
    protected function getIndex(): Index
    {
        return $this->index;
    }

}