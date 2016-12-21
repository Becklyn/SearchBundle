<?php

namespace Becklyn\SearchBundle\Elasticsearch\Request;

use Becklyn\Interfaces\LanguageInterface;
use Becklyn\SearchBundle\Elasticsearch\ElasticsearchClient;
use Becklyn\SearchBundle\Elasticsearch\ElasticsearchRequest;
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
     * @var SearchItem[]
     */
    private $items;



    /**
     * @param string                 $index
     * @param string                 $query
     * @param LanguageInterface|null $language
     * @param SearchItem[]           $items
     */
    public function __construct ($index, string $query, LanguageInterface $language = null, array $items)
    {
        parent::__construct($index, "search");
        $this->query = $query;
        $this->language = $language;
        $this->items = $items;
    }



    /**
     * @inheritdoc
     */
    public function getData () : array
    {
        return array_replace(parent::getData(), [
            "type" => $this->getElasticsearchTypes(),
            "body" => [
                "_source" => [
                    ElasticsearchClient::ENTITY_ID_FIELD,
                ],
                "query" => [
                    "bool" => [
                        "should" => $this->serializeQueryFields(),
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



    private function getElasticsearchTypes ()
    {
        return array_map(
            function (SearchItem $item)
            {
                return $item->getElasticsearchType();
            },
            $this->items
        );
    }



    /**
     * Returns the serialized query fields
     *
     * @return array
     */
    private function serializeQueryFields () : array
    {
        $queryFields = [];

        foreach ($this->items as $item)
        {
            foreach ($item->getFields() as $field)
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

        foreach ($this->items as $item)
        {
            foreach ($item->getFields() as $field)
            {
                $highlightFields[$field->getElasticsearchFieldName()] = [
                    "number_of_fragments" => $field->getNumberOfFragments(),
                ];
            }
        }

        return $highlightFields;
    }
}
