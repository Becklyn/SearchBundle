<?php

namespace Becklyn\SearchBundle\Elasticsearch;

use Becklyn\SearchBundle\Elasticsearch\Request\IndexDocumentRequest;
use Becklyn\SearchBundle\Metadata\MetadataFactory;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Becklyn\SearchBundle\Metadata\Metadata;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;


/**
 * Wrapper around the elasticsearch API
 */
class ElasticsearchClient
{
    const ENTITY_ID_FIELD = "entity-id";
    const ENTITY_TIMESTAMP_FIELD = "entity-timestamp";

    /**
     * @var Client
     */
    private $client;


    /**
     * @var string
     */
    private $index;


    /**
     * @var Metadata
     */
    private $metadata;


    /**
     * @param ElasticsearchApiFactory $apiFactory
     * @param string                  $host
     * @param string                  $index
     * @param MetadataFactory         $metadataFactory
     */
    public function __construct (ElasticsearchApiFactory $apiFactory, string $host, string $index, MetadataFactory $metadataFactory)
    {
        $this->client = $apiFactory->buildApiClient($host);
        $this->index = $index;
        $this->metadata = $metadataFactory->getMetadata();
    }



    /**
     * Sends the given request to elastic search
     *
     * @param ElasticsearchRequest $request
     *
     * @return array|null
     * @throws Missing404Exception
     */
    public function sendRequest (ElasticsearchRequest $request)
    {
        try
        {
            $client = $this->client;
            $namespace = $request->getActionNamespace();
            $action = $request->getAction();

            if (null !== $namespace)
            {
                $client = $client->{$namespace}();
            }

            return $client->{$action}($request->getData());
        }
        catch (Missing404Exception $exception)
        {
            if (!$request->ignoreMissing())
            {
                throw $exception;
            }

            return null;
        }
        catch (NoNodesAvailableException $exception)
        {
            return null;
        }
    }



    /**
     * Sends all given requests to elastic search
     *
     * @param ElasticsearchRequest[] $requests
     *
     * @return array
     */
    public function sendRequests (array $requests)
    {
        $results = [];

        foreach ($requests as $index => $request)
        {
            $results[$index] = $this->sendRequest($request);
        }

        return $results;
    }



    /**
     * Sends index requests in bulk
     *
     * @param IndexDocumentRequest[] $requests
     */
    public function sendBulkIndexRequests (array $requests)
    {
        try
        {
            /** @var IndexDocumentRequest[] $requests */
            $requests = \array_values($requests);
            $currentBulk = [];
            $maxIndex = count($requests) - 1;

            foreach ($requests as $i => $request)
            {
                $data = $request->getData();

                // add header
                $currentBulk[] = [
                    "index" => [
                        "_index" => $data["index"],
                        "_type" => $data["type"],
                        "_id" => $data["id"],
                    ],
                ];

                // add data
                $currentBulk[] = $data["body"];

                // every 250 items -> send
                if ($i % 250 === 0 || $i >= $maxIndex)
                {
                    $this->client->bulk([
                        "body" => $currentBulk,
                    ]);
                    $currentBulk = [];
                }
            }
        }
        catch (NoNodesAvailableException $exception)
        {
            // silently catch exception
        }
    }



    /**
     * @internal
     *
     * @return Client
     */
    public function getClient () : Client
    {
        return $this->client;
    }
}
