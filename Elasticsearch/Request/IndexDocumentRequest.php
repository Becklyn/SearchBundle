<?php

namespace Becklyn\SearchBundle\Elasticsearch\Request;

use Becklyn\SearchBundle\Accessor\EntityValueAccessor;
use Becklyn\SearchBundle\Elasticsearch\ElasticsearchClient;
use Becklyn\SearchBundle\Elasticsearch\ElasticsearchRequest;
use Becklyn\SearchBundle\Entity\SearchableEntityInterface;
use Becklyn\SearchBundle\Metadata\SearchItem;


/**
 * A request to index the given document
 */
class IndexDocumentRequest extends ElasticsearchRequest
{
    /**
     * @var SearchableEntityInterface
     */
    private $entity;


    /**
     * @var SearchItem
     */
    private $item;


    /**
     * @var EntityValueAccessor
     */
    private $valueAccessor;



    /**
     * @param string                    $index
     * @param SearchableEntityInterface $entity
     * @param SearchItem                $item
     * @param EntityValueAccessor       $valueAccessor
     */
    public function __construct ($index, SearchableEntityInterface $entity, SearchItem $item, EntityValueAccessor $valueAccessor)
    {
        parent::__construct($index, "index");

        $this->entity = $entity;
        $this->item = $item;
        $this->valueAccessor = $valueAccessor;
    }



    public function getData () : array
    {
        return array_replace(parent::getData(), [
            "type" => $this->item->getElasticsearchType(),
            "id" => $this->getGlobalEntityId(),
            "body" => $this->serializeEntity(),
        ]);
    }



    /**
     * Returns a globally unique entity id
     *
     * @return string
     */
    private function getGlobalEntityId () : string
    {
        return "{$this->item->getElasticsearchType()}--{$this->entity->getId()}";
    }



    /**
     * Serializes the entity
     *
     * @return array
     */
    private function serializeEntity () : array
    {
        $data = [
            ElasticsearchClient::ENTITY_ID_FIELD => $this->entity->getId(),
            ElasticsearchClient::ENTITY_TIMESTAMP_FIELD => $this->entity->getLastModificationTime()->format("Y-m-d H:i:s"),
        ];

        foreach ($this->item->getFields() as $field)
        {
            $data[$field->getElasticsearchFieldName()] = $this->valueAccessor->getValue($this->entity, $field);
        }

        foreach ($this->item->getFilters() as $filter)
        {
            $data[$filter->getElasticsearchFieldName()] = $this->valueAccessor->getRawValue($this->entity, $filter);
        }

        return $data;
    }
}
