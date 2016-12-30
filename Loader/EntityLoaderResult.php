<?php

namespace Becklyn\SearchBundle\Loader;

use Becklyn\SearchBundle\Entity\SearchableEntityInterface;


class EntityLoaderResult implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    private $entities = [];



    /**
     * @param SearchableEntityInterface[] $directResults
     */
    public function __construct (array $directResults = [])
    {
        foreach ($directResults as $directResult)
        {
            $this->addResult($directResult);
        }
    }


    /**
     * @param SearchableEntityInterface $entity
     * @param int|null                  $forId
     */
    public function addResult (SearchableEntityInterface $entity, int $forId = null)
    {
        if (null === $forId)
        {
            $forId = $entity->getId();
        }

        $this->entities[$forId] = $entity;
    }



    /**
     * Returns the entity for the given ID
     *
     * @param int $id
     *
     * @return SearchableEntityInterface|null
     */
    public function getEntityForId (int $id)
    {
        return $this->entities[$id] ?? null;
    }



    /**
     * Returns all results
     *
     * @return SearchableEntityInterface[]
     */
    public function getAllResults ()
    {
        return $this->entities;
    }



    /**
     * @inheritDoc
     */
    public function getIterator ()
    {
        return new \ArrayIterator($this->entities);
    }



    /**
     * @inheritDoc
     */
    public function count ()
    {
        return count($this->entities);
    }
}
