<?php

namespace Becklyn\SearchBundle\Elasticsearch;

use Becklyn\SearchBundle\Index\Data\IndexData;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Becklyn\SearchBundle\Metadata\Metadata;
use Becklyn\SearchBundle\Metadata\SearchItem;


/**
 * Wrapper around the elasticsearch API
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
     * Indexes the given document.
     *
     * @param IndexData $indexData
     */
    public function indexDocument (IndexData $indexData)
    {
        $this->client->index([
            "index" => $this->index,
            "type" => $indexData->getType(),
            "id" => $indexData->getGlobalItemId(),
            "body" => $indexData->getSerializedData(),
        ]);
    }



    /**
     * Indexes the given documents in bulk
     *
     * @param IndexData[] $indexData
     */
    public function bulkIndexDocuments (array $indexData)
    {
        $currentBulk = [];
        $maxIndex = count($indexData) - 1;

        for ($i = 0; $i <= $maxIndex; $i++)
        {
            $currentItem = $indexData[$i];

            // add header
            $currentBulk[] = [
                "index" => [
                    "_index" => $this->index,
                    "_type" => $currentItem->getType(),
                    "_id" => $currentItem->getGlobalItemId(),
                ],
            ];

            // add data
            $currentBulk[] = $currentItem->getSerializedData();

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
     * Regenerates the index
     */
    public function regenerateIndex ()
    {
        if ($this->indexExists())
        {
            $this->client->indices()->delete([
                "index" => $this->index,
            ]);
        }

        $this->createIndex();
    }



    /**
     * Creates the index, if it doesn't exist
     */
    private function createIndex ()
    {
        if (!$this->indexExists())
        {
            $mappings = [];

            foreach ($this->metadata->getAllItems() as $item)
            {
                $mappings[$item->getElasticsearchType()] = $this->buildMappingForItem($item);
            }

            $this->client->indices()->create([
                "index" => $this->index,
                "body" => [
                    "settings" => [
                        "index" => [
                            "number_of_shards" => 1,
                            "number_of_replicas" => 1,
                        ],
                        "analysis" => [
                            "analyzer" => [
                                "app_analyzer" => [
                                    "tokenizer" => "lowercase",
                                    "filter" => [
                                        "standard",
                                        "lowercase",
                                        "german_stemmer",
                                        "asciifolding",
                                        "shingle_filter",
                                    ],
                                    "type" => "custom",
                                ],
                            ],
                            "filter" => [
                                "german_stemmer" => [
                                    "type" => "stemmer",
                                    "name" => "light_german",
                                ],
                                "shingle_filter" => [
                                    "min_shingle_size" => 2,
                                    "max_shingle_size" => 5,
                                    "type" => "shingle",
                                ],
                            ],
                        ],
                    ],
                    "mappings" => $mappings,
                ]
            ]);
        }
    }



    /**
     * Builds the mapping for the given search item
     *
     * @param SearchItem $item
     *
     * @return array
     */
    private function buildMappingForItem (SearchItem $item) : array
    {
        $mapping = [
            "_source" => [
                "enabled" => true,
            ],
            "properties" => [
                self::TIMESTAMP_FIELD => [
                    "type" => "date",
                    "format" => "yyyy-MM-dd HH:mm:ss",
                ],
                self::ENTITY_ID_FIELD => [
                    "type" => "integer",
                ],
            ],

        ];

        foreach ($item->getFields() as $field)
        {
            $mapping["properties"][$field->getElasticsearchFieldName()] = [
                "type" => "text",
                "analyzer" => "app_analyzer",
                "term_vector" => "with_positions_offsets",
            ];
        }

        return $mapping;
    }



    /**
     * Checks whether the index exists
     *
     * @return bool
     */
    private function indexExists () : bool
    {
        try
        {
            $this->client->indices()->get(["index" => $this->index]);
            return true;
        }
        catch (Missing404Exception $e)
        {
            return false;
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
