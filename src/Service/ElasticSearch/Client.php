<?php


namespace App\Service\ElasticSearch;

use Elastica;
use Elastica\Index;

class Client extends Elastica\Client
{
    public function __construct($config)
    {
        parent::__construct($config);
    }
}