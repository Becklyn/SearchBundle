<?php

namespace Becklyn\SearchBundle\Event;

use Becklyn\SearchBundle\Entity\SearchableEntityInterface;
use Symfony\Component\EventDispatcher\Event;


class BeforeIndexEntityEvent extends Event
{
    const EVENT = "becklyn_search.index:before";


    /**
     * @var SearchableEntityInterface
     */
    private $entity;



    /**
     * @param SearchableEntityInterface $entity
     */
    public function __construct (SearchableEntityInterface $entity)
    {
        $this->entity = $entity;
    }



    /**
     * @return SearchableEntityInterface
     */
    public function getEntity () : SearchableEntityInterface
    {
        return $this->entity;
    }
}
