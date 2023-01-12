<?php

namespace Becklyn\SearchBundle\Search;

use Becklyn\SearchBundle\Elasticsearch\ElasticsearchClient;
use Becklyn\SearchBundle\Elasticsearch\Request\SearchRequest;
use Becklyn\SearchBundle\Entity\LanguageInterface;
use Becklyn\SearchBundle\Exception\MissingLanguageException;
use Becklyn\SearchBundle\Index\Configuration\LanguageConfiguration;
use Becklyn\SearchBundle\Loader\EntityLoader;
use Becklyn\SearchBundle\Metadata\MetadataFactory;
use Becklyn\SearchBundle\Metadata\SearchItem\SearchItemList;
use Becklyn\SearchBundle\Search\Result\SearchHit;
use Becklyn\SearchBundle\Search\Result\SearchResult;
use Becklyn\SearchBundle\Search\Result\SearchResultBuilder;


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
     * @param MetadataFactory       $metadataFactory
     * @param EntityLoader          $entityLoader
     * @param LanguageConfiguration $languageConfiguration
     */
    public function __construct (ElasticsearchClient $client, MetadataFactory $metadataFactory, EntityLoader $entityLoader, LanguageConfiguration $languageConfiguration)
    {
        $this->client = $client;
        $this->allItems = $metadataFactory->getMetadata()->getAllItems();
        $this->entityLoader = $entityLoader;
        $this->languageConfiguration = $languageConfiguration;
    }



    /**
     * Executes the search query
     *
     * @param string            $query
     * @param LanguageInterface $language
     * @param array             $itemClasses FQCN of all classes that should be searched. Empty array searches all classes
     * @param array             $filters
     *
     * @return SearchResult
     * @throws MissingLanguageException
     */
    public function search (string $query, LanguageInterface $language = null, array $itemClasses = [], array $filters = []) : SearchResult
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

            // Right now there seems to be some issue with ElasticSearch not being able to search
            // a larger number of indices in a multi-index search. Hence why we split up each entity
            // into its own search request
            foreach ($localizedItems as $localizedItem)
            {
                $requests[] = new SearchRequest($index, $query, $language, $localizedItem, $filters);
            }
        }

        if (!empty($unlocalizedItems))
        {
            $index = $this->languageConfiguration->getIndexName(null);

            // Right now there seems to be some issue with ElasticSearch not being able to search
            // a larger number of indices in a multi-index search. Hence why we split up each entity
            // into its own search request
            foreach ($unlocalizedItems as $unlocalizedItem)
            {
                $requests[] = new SearchRequest($index, $query, null, $unlocalizedItem, $filters);
            }
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
        $builder = new SearchResultBuilder();

        foreach ($this->groupByType($responses) as $type => $hits)
        {
            $this->addHitsForTypeToSearchResult($builder, $type, $hits);
        }

        return $builder->getSearchResult();
    }



    /**
     * Generates a search result list from the list of raw results
     *
     * @param SearchResultBuilder $searchResultBuilder
     * @param string              $type
     * @param array               $hits
     */
    private function addHitsForTypeToSearchResult (SearchResultBuilder $searchResultBuilder, string $type, array $hits)
    {
        $item = $this->allItems->getByType($type);

        if (null === $item)
        {
            return;
        }

        $ids = array_map(
            function ($hit)
            {
                return $hit["_source"][ElasticsearchClient::ENTITY_ID_FIELD];
            },
            $hits
        );

        $loadedEntities = $this->entityLoader->loadEntities($item, $ids);

        foreach ($hits as $hit)
        {
            $entity = $loadedEntities->getEntityForId($hit["_source"][ElasticsearchClient::ENTITY_ID_FIELD]);

            if (null !== $entity)
            {
                $searchResultBuilder->addHit(new SearchHit($entity, $hit["_score"], $hit["highlight"] ?? []));
            }
        }
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
}
