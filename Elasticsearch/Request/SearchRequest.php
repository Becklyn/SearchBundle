<?php

namespace Becklyn\SearchBundle\Elasticsearch\Request;

use Becklyn\SearchBundle\Elasticsearch\ElasticsearchClient;
use Becklyn\SearchBundle\Elasticsearch\ElasticsearchRequest;
use Becklyn\SearchBundle\Entity\LanguageInterface;
use Becklyn\SearchBundle\Metadata\SearchItem;


/**
 * Represents an elasticsearch "search" request
 */
class SearchRequest extends ElasticsearchRequest
{
    /**
     * @var string
     */
    private $query;


    /**
     * @var LanguageInterface
     */
    private $language;


    /**
     * @var SearchItem
     */
    private $item;


    /**
     * @var array
     */
    private $filters;



    /**
     * @param string                 $index
     * @param string                 $query
     * @param LanguageInterface|null $language
     * @param SearchItem             $item
     * @param array                  $filters
     */
    public function __construct ($index, string $query, LanguageInterface $language = null, SearchItem $item, array $filters = [])
    {
        parent::__construct($index, "search");
        $this->query = $query;
        $this->language = $language;
        $this->item = $item;
        $this->filters = $filters;
    }



    /**
     * @inheritdoc
     */
    public function getData () : array
    {
        return array_replace(parent::getData(), [
            "type" => $this->item->getElasticsearchType(),
            "body" => [
                "_source" => [
                    ElasticsearchClient::ENTITY_ID_FIELD,
                ],
                "query" => [
                    "bool" => [
                        "should" => $this->serializeQueryFields(),
                        "minimum_should_match" => 1,
                        "filter" => $this->serializeFilters(),
                    ],
                ],
                "highlight" => [
                    "pre_tags" => ["<mark>"],
                    "post_tags" => ["</mark>"],
                    "fields" => $this->serializeHighlightFields(),
                ],
            ]
        ]);
    }



    /**
     * Returns the serialized query fields
     *
     * @return array
     */
    private function serializeQueryFields () : array
    {
        $queryFields = [];

        foreach ($this->item->getFields() as $field)
        {
            $queryFields[] = [
                "match" => [
                    $field->getElasticsearchFieldName() => [
                        "query" => $this->query,
                        "boost" => $field->getWeight(),
                    ],
                ],
            ];
        }

        return $queryFields;
    }



    /**
     * Serializes the highlight fields
     *
     * @return array
     */
    private function serializeHighlightFields () : array
    {
        $highlightFields = [];

        foreach ($this->item->getFields() as $field)
        {
            $highlightFields[$field->getElasticsearchFieldName()] = [
                "number_of_fragments" => $field->getNumberOfFragments(),
            ];
        }

        return $highlightFields;
    }



    /**
     * Serializes the filters
     *
     * @return array
     */
    private function serializeFilters () : array
    {
        if (empty($this->filters))
        {
            return [];
        }

        $terms = [];

        foreach ($this->filters as $name => $value)
        {
            $terms["filter-{$name}"] = $value;
        }

        return [
            "term" => $terms,
        ];
    }
}
