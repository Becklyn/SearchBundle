<?php

namespace Becklyn\SearchBundle\Search;

use Becklyn\Interfaces\LanguageInterface;
use Becklyn\SearchBundle\Elasticsearch\ElasticsearchClient;
use Becklyn\SearchBundle\Elasticsearch\Request\SearchRequest;
use Becklyn\SearchBundle\Exception\MissingLanguageException;
use Becklyn\SearchBundle\Exception\UnknownItemException;
use Becklyn\SearchBundle\Index\Configuration\LanguageConfiguration;
use Becklyn\SearchBundle\Loader\EntityLoader;
use Becklyn\SearchBundle\Metadata\Metadata;
use Becklyn\SearchBundle\Metadata\SearchItem;
use Becklyn\SearchBundle\Metadata\SearchItem\SearchItemList;
use Becklyn\SearchBundle\Search\Result\SearchHit;
use Becklyn\SearchBundle\Search\Result\EntitySearchHits;
use Becklyn\SearchBundle\Search\Result\SearchResult;


/**
 * Service for searching entities
 */
class SearchClient
{
    /**
     * @var ElasticsearchClient
     */
    private $client;


    /**
     * @var SearchItemList
     */
    private $allItems;


    /**
     * @var EntityLoader
     */
    private $entityLoader;


    /**
     * @var LanguageConfiguration
     */
    private $languageConfiguration;



    /**
     * @param ElasticsearchClient   $client
     * @param Metadata              $metadata
     * @param EntityLoader          $entityLoader
     * @param LanguageConfiguration $languageConfiguration
     */
    public function __construct (ElasticsearchClient $client, Metadata $metadata, EntityLoader $entityLoader, LanguageConfiguration $languageConfiguration)
    {
        $this->client = $client;
        $this->allItems = $metadata->getAllItems();
        $this->entityLoader = $entityLoader;
        $this->languageConfiguration = $languageConfiguration;
    }



    /**
     * Executes the search query
     *
     * @param string            $query
     * @param LanguageInterface $language
     * @param array             $itemClasses FQCN of all classes that should be searched. Empty array searches all classes
     *
     * @return SearchResult
     * @throws MissingLanguageException
     */
    public function search (string $query, LanguageInterface $language = null, array $itemClasses = []) : SearchResult
    {
        $items = $this->allItems->filterByClass($itemClasses);
        $localizedItems = $items->getLocalizedItems();
        $unlocalizedItems = $items->getUnlocalizedItems();
        $requests = [];

        if (!empty($localizedItems))
        {
            if (null === $language)
            {
                throw new MissingLanguageException($localizedItems);
            }

            $index = $this->languageConfiguration->getIndexName($language->getCode());
            $requests[] = new SearchRequest($index, $query, $language, $localizedItems);
        }

        if (!empty($unlocalizedItems))
        {
            $index = $this->languageConfiguration->getIndexName(null);
            $requests[] = new SearchRequest($index, $query, null, $unlocalizedItems);
        }

        return $this->buildSearchResult(
            $this->client->sendRequests($requests),
            $language
        );
    }



    /**
     * @param array             $responses
     * @param LanguageInterface $language
     *
     * @return SearchResult
     */
    private function buildSearchResult (array $responses, LanguageInterface $language = null) : SearchResult
    {
        $entityHits = [];

        foreach ($this->groupByType($responses) as $type => $hits)
        {
            $resultList = $this->buildEntitySearchHits($type, $hits, $language);

            if (null !== $resultList)
            {
                $entityHits[] = $resultList;
            }
        }

        return new SearchResult($entityHits);
    }



    /**
     * Generates a search result list from the list of raw results
     *
     * @param string            $type
     * @param array             $hits
     * @param LanguageInterface $language
     *
     * @return EntitySearchHits|null
     */
    private function buildEntitySearchHits (string $type, array $hits, LanguageInterface $language = null)
    {
        $item = $this->allItems->getByType($type);
        $results = [];

        if (null === $item)
        {
            return null;
        }

        $ids = array_map(
            function ($hit)
            {
                return $hit["_source"][ElasticsearchClient::ENTITY_ID_FIELD];
            },
            $hits
        );

        $entities = $this->entityLoader->loadEntities($item, $ids);

        foreach ($hits as $hit)
        {
            $entity = $entities[$hit["_source"][ElasticsearchClient::ENTITY_ID_FIELD]] ?? null;

            if (null !== $entity)
            {
                $results[] = new SearchHit($entity, $hit["_score"], $hit["highlight"] ?? []);
            }
        }

        return !empty($results)
            ? new EntitySearchHits(
                $item->getFqcn(),
                $results,
                $item->isLocalized() ? $language : null
            )
            : null;
    }


    /**
     * Groups the search results by type
     *
     * @param array $responses
     *
     * @return array
     */
    private function groupByType (array $responses)
    {
        $groupedResults = [];

        foreach ($responses as $response)
        {
            if (!isset($response["hits"]["hits"]) || empty($response["hits"]["hits"]))
            {
                continue;
            }

            foreach ($response["hits"]["hits"] as $hit)
            {
                $groupedResults[$hit["_type"]][] = $hit;
            }
        }

        return $groupedResults;
    }



    /**
     * Loads the entities of the result
     *
     * @param array        $groupedResults
     * @param SearchItem[] $itemsToSearch
     *
     * @return array.<SearchResultItem[]>
     */
    private function loadResults (array $groupedResults, array $itemsToSearch) : array
    {
        $loadedResults = [];

        foreach ($groupedResults as $type => $hits)
        {
            $item = $itemsToSearch[$type];
            $ids = array_column($hits, ElasticsearchClient::ENTITY_ID_FIELD);
            $loadedEntities = $this->entityLoader->loadEntities($item, $ids);

            foreach ($hits as $hit)
            {
                $entity = $loadedEntities[$hit[ElasticsearchClient::ENTITY_ID_FIELD]] ?? null;

                if (null === $entity)
                {
                    continue;
                }

                $loadedResults[$item->getFqcn()][] = new SearchResultItem(
                    $entity,
                    $hit["_score"],
                    array_merge(...array_values($hit["highlight"]))
                );
            }
        }

        return $loadedResults;
    }



    /**
     * Groups the raw elasticsearch results by type
     *
     * @param array $rawResults
     *
     * @return array
     */
    private function groupRawResultsByType (array $rawResults) : array
    {
        $grouped = [];

        foreach ($rawResults as $rawResult)
        {
            $grouped[$rawResult["_type"]][] = $rawResult;
        }

        return $grouped;
    }




    /**
     * Builds the query parameters, that will be passed to elasticsearch
     *
     * @param string       $query
     * @param SearchItem[] $searchItems
     *
     * @return array
     */
    private function buildQuery (string $query, array $searchItems) : array
    {
        $queryFields = [];
        $highlightFields = [];

        foreach ($searchItems as $item)
        {
            foreach ($item->getFields() as $field)
            {
                $queryFields[] = [
                    "match" => [
                        $field->getElasticsearchFieldName() => [
                            "query" => $query,
                            "boost" => $field->getWeight(),
                        ],
                    ],
                ];

                $highlightFields[$field->getElasticsearchFieldName()] = [
                    "number_of_fragments" => $field->getNumberOfFragments(),
                ];
            }
        }

        return [
            "_source" => false,
            "query" => [
                "bool" => [
                    "should" => $queryFields,
                ],
            ],
            "highlight" => [
                "pre_tags" => ["<mark>"],
                "post_tags" => ["</mark>"],
                "fields" => $highlightFields,
            ],
        ];
    }
}
