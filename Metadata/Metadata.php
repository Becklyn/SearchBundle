<?php

namespace Becklyn\SearchBundle\Metadata;

use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;


/**
 * The metadata of the searchable items and their properties + methods
 */
class Metadata
{
    const CACHE_KEY = "becklyn_search.metadata";

    /**
     * @var SearchItem[]
     */
    private $items = [];


    /**
     * @var AdapterInterface
     */
    private $cachePool;


    /**
     * @var CacheItemInterface
     */
    private $cache;



    /**
     * @param AdapterInterface $cachePool
     */
    public function __construct (AdapterInterface $cachePool)
    {
        $this->cachePool = $cachePool;
        $this->cache = $cachePool->getItem(self::CACHE_KEY);

        if ($this->cache->isHit())
        {
            $this->items = $this->cache->get();
        }
    }


    /**
     * @param SearchItem $item
     */
    public function add (SearchItem $item)
    {
        $this->items[$item->getFqcn()] = $item;
        $this->updateCache();
    }



    /**
     * Returns the indexed item
     *
     * @param string $fqcn
     *
     * @return null|SearchItem
     */
    public function get (string $fqcn)
    {
        return $this->items[$fqcn] ?? null;
    }



    /**
     * Clears all stored metadata
     */
    public function clear ()
    {
        $this->items = [];
        $this->updateCache();
    }



    /**
     * Updates the cache
     */
    private function updateCache ()
    {
        $this->cache->set($this->items);
        $this->cachePool->save($this->cache);
    }



    /**
     * @return SearchItem[]
     */
    public function getAllItems () : array
    {
        return $this->items;
    }
}
