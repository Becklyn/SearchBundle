<?php

namespace Becklyn\SearchBundle\Elasticsearch;

use Becklyn\SearchBundle\Elasticsearch\Request\IndexDocumentRequest;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Becklyn\SearchBundle\Metadata\Metadata;


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
     * @param string   $host
     * @param string   $index
     * @param Metadata $metadata
     */
    public function __construct (string $host, string $index, Metadata $metadata)
    {
        $this->client = ClientBuilder::create()
            ->setHosts([$host])
            ->build();

        $this->index = $index;
        $this->metadata = $metadata;
    }



    /**
     * Sends the given request to elastic search
     *
     * @param ElasticsearchRequest $request
     *
     * @throws Missing404Exception
     */
    public function sendRequest (ElasticsearchRequest $request)
    {
        dump("{$request->getActionNamespace()}::{$request->getAction()}()");
        dump($request->getData());
        // return;

        try
        {
            $client = $this->client;
            $namespace = $request->getActionNamespace();
            $action = $request->getAction();

            if (null !== $namespace)
            {
                $client = $client->{$namespace}();
            }

            $client->{$action}($request->getData());
        }
        catch (Missing404Exception $exception)
        {
            if (!$request->ignoreMissing())
            {
                throw $exception;
            }
        }
    }



    /**
     * Sends all given requests to elastic search
     *
     * @param ElasticsearchRequest[] $requests
     */
    public function sendRequests (array $requests)
    {
        foreach ($requests as $request)
        {
            $this->sendRequest($request);
        }
    }



    /**
     * Sends index requests in bulk
     *
     * @param IndexDocumentRequest[] $requests
     */
    public function sendBulkIndexRequests (array $requests)
    {
        $currentBulk = [];
        $maxIndex = count($requests) - 1;

        for ($i = 0; $i <= $maxIndex; $i++)
        {
            $request = $requests[$i];
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

            // every 1000 items -> send
            if ($i % 1000 === 0 || $i >= $maxIndex)
            {
                $this->client->bulk([
                    "body" => $currentBulk,
                ]);
                $currentBulk = [];
            }
        }
    }



    /**
     * Searches with the given parameters
     *
     * @param string[] $types
     * @param array    $body
     *
     * @return array
     */
    public function search (array $types, array $body) : array
    {
        $results = $this->client->search([
            "index" => $this->index,
            "type" => $types,
            "body" => $body,
        ]);

        return $results["hits"];
    }
}
