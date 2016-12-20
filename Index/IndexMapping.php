<?php

namespace Becklyn\SearchBundle\Index;

use Becklyn\SearchBundle\Elasticsearch\ElasticsearchClient;


/**
 * Service for (re)creating the index
 */
class IndexMapping
{
    /**
     * @var ElasticsearchClient
     */
    private $client;



    /**
     * @param ElasticsearchClient $client
     */
    public function __construct (ElasticsearchClient $client)
    {
        $this->client = $client;
    }



    /**
     * Regenerates the index mapping
     */
    public function regenerateIndex ()
    {
        $this->client->regenerateIndex();
    }
}
