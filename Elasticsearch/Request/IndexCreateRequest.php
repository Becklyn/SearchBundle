<?php

namespace Becklyn\SearchBundle\Elasticsearch\Request;

use Becklyn\SearchBundle\Elasticsearch\ElasticsearchClient;
use Becklyn\SearchBundle\Elasticsearch\ElasticsearchRequest;
use Becklyn\SearchBundle\Index\Configuration\AnalysisConfiguration;
use Becklyn\SearchBundle\Index\Configuration\LanguageConfiguration;
use Becklyn\SearchBundle\Metadata\SearchItem;


/**
 * A request that creates the index for the given language
 */
class IndexCreateRequest extends ElasticsearchRequest
{
    /**
     * @var SearchItem[]
     */
    private $searchItems;


    /**
     * @var string|null
     */
    private $language;


    /**
     * @var LanguageConfiguration
     */
    private $languageConfiguration;


    /**
     * @var AnalysisConfiguration
     */
    private $analysisConfiguration;



    /**
     * @param string                $language
     * @param array                 $searchItems
     * @param LanguageConfiguration $languageConfiguration
     * @param AnalysisConfiguration $analysisConfiguration
     */
    public function __construct (string $language = null, array $searchItems, LanguageConfiguration $languageConfiguration, AnalysisConfiguration $analysisConfiguration)
    {
        parent::__construct($languageConfiguration->getIndexName($language), "create", "indices");

        $this->language = $language;
        $this->searchItems = $searchItems;
        $this->languageConfiguration = $languageConfiguration;
        $this->analysisConfiguration = $analysisConfiguration;
    }



    /**
     * @inheritdoc
     */
    public function getData () : array
    {
        $indexAnalyzer = $this->languageConfiguration->getIndexAnalyzer($this->language);
        $searchAnalyzer = $this->languageConfiguration->getSearchAnalyzer($this->language);

        $usedAnalyzers = [
            $indexAnalyzer => $this->analysisConfiguration->getAnalyzer($indexAnalyzer),
        ];

        if ($searchAnalyzer !== $indexAnalyzer)
        {
            $usedAnalyzers[$searchAnalyzer] = $this->analysisConfiguration->getAnalyzer($searchAnalyzer);
        }

        return array_replace(parent::getData(), [
            "body" => [
                "settings" => [
                    "index" => [
                        "number_of_shards" => 1,
                        "number_of_replicas" => 1,
                    ],
                    "analysis" => [
                        "analyzer" => $usedAnalyzers,
                        "filter" => $this->findCustomFilters($usedAnalyzers),
                    ],
                ],
                "mappings" => $this->buildMappings($indexAnalyzer, $searchAnalyzer),
            ]
        ]);
    }



    /**
     * Finds all custom filters
     *
     * @param array $analyzerList
     *
     * @return array
     */
    private function findCustomFilters (array $analyzerList) : array
    {
        $customFilters = [];

        foreach ($analyzerList as $analyzer)
        {
            if (!isset($analyzer["filter"]) || empty($analyzer["filter"]))
            {
                return [];
            }

            foreach ($analyzer["filter"] as $filter)
            {
                $filterConfiguration = $this->analysisConfiguration->getFilter($filter);

                // all filters that aren't explicitly defined are assumed to be built-in
                if (null !== $filterConfiguration)
                {
                    $customFilters[$filter] = $filterConfiguration;
                }
            }
        }

        return $customFilters;
    }



    /**
     * Builds the complete mapping for this index
     *
     * @param string $indexAnalyzer
     * @param string $searchAnalyzer
     *
     * @return array
     */
    private function buildMappings (string $indexAnalyzer, string $searchAnalyzer)
    {
        $mapping = [];

        foreach ($this->searchItems as $item)
        {
            $mapping[$item->getElasticsearchType()] = $this->buildMappingForItem($item, $indexAnalyzer, $searchAnalyzer);
        }

        return $mapping;
    }



    /**
     * Builds the mapping for the given item
     *
     * @param SearchItem $item
     * @param string     $indexAnalyzer
     * @param string     $searchAnalyzer
     *
     * @return array
     */
    private function buildMappingForItem (SearchItem $item, string $indexAnalyzer, string $searchAnalyzer)
    {
        $mapping = [
            "_source" => [
                "enabled" => true,
            ],
            "properties" => [
                ElasticsearchClient::ENTITY_TIMESTAMP_FIELD => [
                    "type" => "date",
                    "format" => "yyyy-MM-dd HH:mm:ss",
                ],
                ElasticsearchClient::ENTITY_ID_FIELD => [
                    "type" => "integer",
                ],
            ],
        ];

        foreach ($item->getFields() as $field)
        {
            $mapping["properties"][$field->getElasticsearchFieldName()] = [
                "type" => "text",
                "analyzer" => $indexAnalyzer,
                "search_analyzer" => $searchAnalyzer,
                "term_vector" => "with_positions_offsets",
            ];
        }

        return $mapping;
    }

}
