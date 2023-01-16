<?php

namespace Becklyn\SearchBundle\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;


class ElasticsearchApiFactory
{
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
