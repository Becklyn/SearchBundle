<?php

namespace Becklyn\SearchBundle\Event;

use Becklyn\SearchBundle\Entity\SearchableEntityInterface;
use Symfony\Component\EventDispatcher\Event;


class IndexEntityEvent extends Event
{
    const EVENT = "becklyn_search.index";


    /**
     * @var array
     */
    private $serializedData;


    /**
     * @var SearchableEntityInterface
     */
    private $entity;



    public function __construct (array $serializedData, SearchableEntityInterface $entity)
    {
        $this->serializedData = $serializedData;
        $this->entity = $entity;
    }



    /**
     * @return array
     */
    public function getData () : array
    {
        return $this->serializedData;
    }



    /**
     * @param array $data
     */
    public function setData (array $data)
    {
        $this->serializedData = $data;
    }



    /**
     * @return SearchableEntityInterface
     */
    public function getEntity () : SearchableEntityInterface
    {
        return $this->entity;
    }
}
