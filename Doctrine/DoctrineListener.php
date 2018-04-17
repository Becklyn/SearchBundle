<?php

namespace Becklyn\SearchBundle\Doctrine;

use Becklyn\SearchBundle\Entity\SearchableEntityInterface;
use Becklyn\SearchBundle\Metadata\Metadata;
use Becklyn\SearchBundle\Metadata\MetadataFactory;
use Becklyn\SearchBundle\Metadata\SearchItem;
use Becklyn\SearchBundle\Search\SearchIndexer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;


/**
 * Handles the automatic index update on doctrine events
 */
class DoctrineListener implements EventSubscriber
{
    /**
     * @var Metadata
     */
    private $metadata;


    /**
     * @var SearchIndexer
     */
    private $indexer;


    /**
     * @param MetadataFactory $metadataFactory
     * @param SearchIndexer   $indexer
     */
    public function __construct (MetadataFactory $metadataFactory, SearchIndexer $indexer)
    {
        $this->metadata = $metadataFactory->getMetadata();
        $this->indexer = $indexer;
    }



    /**
     * Event handler for the "postPersist" event
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist (LifecycleEventArgs $args)
    {
        $this->handleDoctrineEvent($args);
    }



    /**
     * Event handler for the "postUpdate" event
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate (LifecycleEventArgs $args)
    {
        $this->handleDoctrineEvent($args);
    }



    /**
     * Handles all doctrine events
     *
     * @param LifecycleEventArgs $args
     */
    public function handleDoctrineEvent (LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        /** @var $item SearchItem */
        foreach ($this->metadata->getAllItems() as $item)
        {
            if (is_a($entity, $item->getFqcn()) && $item->isAutoIndexed())
            {
                /** @var SearchableEntityInterface $entity */
                $this->indexer->index($entity);
                return;
            }
        }

    }


    /**
     * @inheritDoc
     */
    public function getSubscribedEvents ()
    {
        return [
            "postPersist",
            "postUpdate",
        ];
    }
}
