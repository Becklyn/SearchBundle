<?php

namespace Becklyn\SearchBundle\Search;

use Becklyn\SearchBundle\Accessor\EntityValueAccessor;
use Becklyn\SearchBundle\Elasticsearch\ElasticsearchClient;
use Becklyn\SearchBundle\Elasticsearch\Request\IndexDocumentRequest;
use Becklyn\SearchBundle\Entity\SearchableEntityInterface;
use Becklyn\SearchBundle\Index\Configuration\LanguageConfiguration;
use Becklyn\SearchBundle\Metadata\Metadata;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;


/**
 * Service for indexing entities
 */
class SearchIndexer
{
    /**
     * @var ElasticsearchClient
     */
    private $client;


    /**
     * @var Metadata
     */
    private $metadata;


    /**
     * @var PropertyAccessor
     */
    private $accessor;


    /**
     * @var LanguageConfiguration
     */
    private $languageConfiguration;


    /**
     * @var EntityValueAccessor
     */
    private $valueAccessor;


    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;



    /**
     * @param ElasticsearchClient      $client
     * @param Metadata                 $metadata
     * @param LanguageConfiguration    $languageConfiguration
     * @param EntityValueAccessor      $valueAccessor
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct (ElasticsearchClient $client, Metadata $metadata, LanguageConfiguration $languageConfiguration, EntityValueAccessor $valueAccessor, EventDispatcherInterface $dispatcher)
    {
        $this->client = $client;
        $this->metadata = $metadata;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->languageConfiguration = $languageConfiguration;
        $this->valueAccessor = $valueAccessor;
        $this->dispatcher = $dispatcher;
    }



    /**
     * Indexes the given entity
     *
     * @param SearchableEntityInterface $entity
     */
    public function index (SearchableEntityInterface $entity)
    {
        $request = $this->generateIndexRequest($entity);

        if (null !== $request)
        {
            $this->client->sendRequest($request);
        }
    }



    /**
     * Bulk indexes the given entities
     *
     * @param SearchableEntityInterface[] $entities
     */
    public function bulkIndex (array $entities)
    {
        $requests = [];

        foreach ($entities as $entity)
        {
            $request = $this->generateIndexRequest($entity);

            if (null !== $request)
            {
                $requests[] = $request;
            }
        }

        if (!empty($requests))
        {
            $this->client->sendBulkIndexRequests($requests);
        }
    }



    /**
     * Generates an index requests
     *
     * @param SearchableEntityInterface $entity
     *
     * @return IndexDocumentRequest|null
     */
    private function generateIndexRequest (SearchableEntityInterface $entity)
    {
        $item = $this->metadata->get(ClassUtils::getClass($entity));

        if (null === $item)
        {
            return null;
        }

        $index = $this->languageConfiguration->getIndexForEntity($entity);
        return new IndexDocumentRequest($index, $entity, $item, $this->valueAccessor, $this->dispatcher);
    }
}
