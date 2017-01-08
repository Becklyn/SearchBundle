<?php

namespace Becklyn\SearchBundle\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;


class ElasticsearchApiFactory
{
    /**
     * Builds the API client
     *
     * @param string $host
     *
     * @return Client
     */
    public function buildApiClient (string $host) : Client
    {
        $builder = ClientBuilder::create()
            ->setHosts([$host]);
        $this->addOptionsToClientBuilder($builder);

        return $builder->build();
    }



    /**
     * Adds options to the client builder
     *
     * @param ClientBuilder $builder
     */
    protected function addOptionsToClientBuilder (ClientBuilder $builder)
    {
        // no additional methods set
    }
}
