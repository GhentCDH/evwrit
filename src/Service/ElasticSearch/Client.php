<?php


namespace App\Service\ElasticSearch;

use Elastica;
use Elastica\Index;

class Client extends Elastica\Client
{
    protected $indexPrefix;

    /**
     * @param $config
     * @param null $indexPrefix
     */
    public function __construct($config , $indexPrefix = null)
    {
        $this->indexPrefix = $indexPrefix;
        parent::__construct($config);
    }

    /**
     * @param string $name
     * @return Index
     */
    public function getIndex($name): Index
    {
        return parent::getIndex(($this->indexPrefix ? $this->indexPrefix .'_' : '').$name );
    }
}