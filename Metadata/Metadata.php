<?php

namespace Becklyn\SearchBundle\Metadata;

use Becklyn\SearchBundle\Metadata\SearchItem\SearchItemList;
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
     * @var bool
     */
    private $initialized = false;



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
            $this->initialized = true;
        }
    }


    /**
     * @param SearchItem $item
     */
    public function add (SearchItem $item)
    {
        $this->items[$item->getFqcn()] = $item;
        $this->updateCache();
        $this->initialized = true;
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
        $this->initialized = false;
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
     * Returns a list of all search items
     *
     * @return SearchItemList
     */
    public function getAllItems () : SearchItemList
    {
        return new SearchItemList($this->items);
    }


    /**
     * @return bool
     */
    public function isInitialized () : bool
    {
        return $this->initialized;
    }
}
