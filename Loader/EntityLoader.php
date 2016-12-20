<?php

namespace Becklyn\SearchBundle\Loader;

use Becklyn\SearchBundle\Exception\InvalidEntityLoaderException;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Becklyn\SearchBundle\Entity\SearchableEntityInterface;
use Becklyn\SearchBundle\Metadata\SearchItem;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Loader that loads entities for the search items
 */
class EntityLoader
{
    const LOAD_ALL_ENTITIES = null;


    /**
     * @var Registry
     */
    private $doctrine;


    /**
     * @var ContainerInterface
     */
    private $container;



    /**
     * @param Registry           $doctrine
     * @param ContainerInterface $container
     */
    public function __construct (Registry $doctrine, ContainerInterface $container)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
    }



    /**
     * Loads the entities for the given search item
     *
     * @param SearchItem $item
     * @param int[]|null $ids if provided, only entities with one of these ids need to be loaded
     *
     * @return SearchableEntityInterface[]
     */
    public function loadEntities (SearchItem $item, array $ids = null) : array
    {
        $loader = $item->getLoader();

        $entities = null !== $loader
            ? $this->loadUsingLoader($item, $ids)
            : $this->loadWithDefaultLoader($item, $ids);

        return $this->indexById($entities);
    }



    /**
     * Indexes the given list of entities by id
     *
     * @param SearchableEntityInterface[] $entities
     *
     * @return SearchableEntityInterface[]
     */
    private function indexById (array $entities)
    {
        $indexed = [];

        foreach ($entities as $entity)
        {
            $indexed[$entity->getId()] = $entity;
        }

        return $indexed;
    }



    /**
     * Loads the given entities with a custom loader
     *
     * @param string     $loader
     * @param int[]|null $ids
     *
     * @return SearchableEntityInterface[]
     * @throws InvalidEntityLoaderException
     */
    private function loadUsingLoader (string $loader, array $ids = null)
    {
        $serviceCall = explode(":", $loader);

        if (2 !== count($serviceCall))
        {
            throw new InvalidEntityLoaderException($loader);
        }

        $service = $this->container->get($serviceCall[0]);
        $method = $serviceCall[1];
        return $service->$method($ids);
    }



    /**
     * @param SearchItem $item
     * @param int[]|null $ids
     *
     * @return SearchableEntityInterface[]
     */
    private function loadWithDefaultLoader (SearchItem $item, array $ids = null) : array
    {
        $repository = $this->doctrine->getRepository($item->getFqcn());

        return null !== $ids
            ? $repository->findBy(["id" => $ids])
            : $repository->findAll();
    }
}
