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
     * @return EntityLoaderResult
     */
    public function loadEntities (SearchItem $item, array $ids = null) : EntityLoaderResult
    {
        $loader = $item->getLoader();

        return null !== $loader
            ? $this->loadUsingLoader($item, $ids)
            : $this->loadWithDefaultLoader($item, $ids);
    }



    /**
     * Loads the given entities with a custom loader
     *
     * @param SearchItem $item
     * @param int[]|null $ids
     *
     * @return EntityLoaderResult
     * @throws InvalidEntityLoaderException
     */
    private function loadUsingLoader (SearchItem $item, array $ids = null) : EntityLoaderResult
    {
        $serviceCall = explode(":", $item->getLoader());

        if (2 !== count($serviceCall))
        {
            throw new InvalidEntityLoaderException($loader);
        }

        $service = $this->container->get($serviceCall[0]);
        $method = $serviceCall[1];
        return $service->$method($item, $ids);
    }



    /**
     * @param SearchItem $item
     * @param int[]|null $ids
     *
     * @return EntityLoaderResult
     */
    private function loadWithDefaultLoader (SearchItem $item, array $ids = null) : EntityLoaderResult
    {
        $repository = $this->doctrine->getRepository($item->getFqcn());

        $result = null !== $ids
            ? $repository->findBy(["id" => $ids])
            : $repository->findAll();

        return new EntityLoaderResult($result);
    }
}
