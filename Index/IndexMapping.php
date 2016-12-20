<?php

namespace Becklyn\SearchBundle\Index;

use Becklyn\SearchBundle\Elasticsearch\ElasticsearchClient;
use Becklyn\SearchBundle\Elasticsearch\ElasticsearchRequest;
use Becklyn\SearchBundle\Elasticsearch\Request\IndexCreateRequest;
use Becklyn\SearchBundle\Elasticsearch\Request\IndexDeleteRequest;
use Becklyn\SearchBundle\Index\Configuration\AnalysisConfiguration;
use Becklyn\SearchBundle\Index\Configuration\LanguageConfiguration;
use Becklyn\SearchBundle\Metadata\Metadata;


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
     * @var Metadata
     */
    private $metadata;


    /**
     * @var AnalysisConfiguration
     */
    private $analysisConfiguration;


    /**
     * @var LanguageConfiguration
     */
    private $languageConfiguration;



    /**
     * @param ElasticsearchClient   $client
     * @param Metadata              $metadata
     * @param AnalysisConfiguration $analysisConfiguration
     * @param LanguageConfiguration $languageConfiguration
     */
    public function __construct (ElasticsearchClient $client, Metadata $metadata, AnalysisConfiguration $analysisConfiguration, LanguageConfiguration $languageConfiguration)
    {
        $this->client = $client;
        $this->metadata = $metadata;
        $this->analysisConfiguration = $analysisConfiguration;
        $this->languageConfiguration = $languageConfiguration;
    }



    /**
     * Regenerates the index mapping
     */
    public function regenerateIndex ()
    {
        $deleteRequests = $this->generateDeleteRequests();
        $createRequests = $this->generateCreateRequests();

        $this->client->sendRequests($deleteRequests);
        $this->client->sendRequests($createRequests);
    }



    /**
     * Generates all "delete index" requests
     *
     * @return ElasticsearchRequest[]
     */
    private function generateDeleteRequests () : array
    {
        $requests = [];

        foreach ($this->languageConfiguration->getAllLanguages() as $language)
        {
            $index = $this->languageConfiguration->getIndexName($language);
            $requests[] = new IndexDeleteRequest($index);
        }

        return $requests;
    }



    /**
     * Generates all "create index" requests
     *
     * @return array
     */
    private function generateCreateRequests () : array
    {
        $requests = [];

        foreach ($this->languageConfiguration->getAllLanguages() as $language)
        {
            $items = $language !== null
                ? $this->metadata->getAllLocalizedItems()
                : $this->metadata->getAllUnlocalizedItems();

            if (empty($items))
            {
                continue;
            }

            $requests[] = new IndexCreateRequest($language, $items, $this->languageConfiguration, $this->analysisConfiguration);
        }

        return $requests;
    }
}
