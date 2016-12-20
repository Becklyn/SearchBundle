<?php

namespace Becklyn\SearchBundle\Entity;


/**
 * An interface for entities which can be searched
 */
interface SearchableEntityInterface
{
    /**
     * @return int
     */
    public function getId ();



    /**
     * Returns the time of the last modification of the item
     *
     * @return \DateTimeInterface
     */
    public function getLastModificationTime () : \DateTimeInterface;
}
