<?php

namespace Becklyn\SearchBundle\Elasticsearch\Request;

use Becklyn\SearchBundle\Elasticsearch\ElasticsearchClient;
use Becklyn\SearchBundle\Elasticsearch\ElasticsearchRequest;
use Becklyn\SearchBundle\Entity\SearchableEntityInterface;
use Becklyn\SearchBundle\Metadata\SearchItem;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;


class IndexDocumentRequest extends ElasticsearchRequest
{
    /**
     * @var PropertyAccessor
     */
    private $accessor;


    /**
     * @var SearchableEntityInterface
     */
    private $entity;


    /**
     * @var SearchItem
     */
    private $item;



    /**
     * @param string                    $index
     * @param SearchableEntityInterface $entity
     * @param SearchItem                $item
     */
    public function __construct ($index, SearchableEntityInterface $entity, SearchItem $item)
    {
        parent::__construct($index, "index");

        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->entity = $entity;
        $this->item = $item;
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
            $data[$field->getElasticsearchFieldName()] = $this->accessor->getValue($this->entity, $field->getName());
        }

        return $data;
    }
}
