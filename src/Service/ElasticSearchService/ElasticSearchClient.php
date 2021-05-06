<?php


namespace App\Service\ElasticSearchService;

use Elastica\Client;
use Elastica\Index;

class ElasticSearchClient extends Client
{
    protected $indexPrefix;

    public function __construct($config , $indexPrefix = null)
    {
        $this->indexPrefix = $indexPrefix;
        parent::__construct($config);
    }

    public function getIndex($name): Index
    {
        return parent::getIndex(($this->indexPrefix ? $this->indexPrefix .'_' : '').$name );
    }
}