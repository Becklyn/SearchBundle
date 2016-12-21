<?php

namespace Becklyn\SearchBundle\Metadata\SearchItem;

use Becklyn\SearchBundle\Exception\UnknownItemException;
use Becklyn\SearchBundle\Metadata\SearchItem;


/**
 * A list of search items
 */
class SearchItemList implements \IteratorAggregate
{
    /**
     * @var SearchItem[]
     */
    private $searchItems;


    /**
     * @var SearchItem[]
     */
    private $fqcnMap = [];



    /**
     * @param SearchItem[] $searchItems
     */
    public function __construct (array $searchItems)
    {
        $this->searchItems = $searchItems;

        foreach ($searchItems as $searchItem)
        {
            $this->fqcnMap[$searchItem->getFqcn()] = $searchItem;
        }
    }



    /**
     * Returns all localized items
     *
     * @return SearchItem[]
     */
    public function getLocalizedItems () : array
    {
        return $this->filterItems(true);
    }



    /**
     * Returns all unlocalized items
     *
     * @return SearchItem[]
     */
    public function getUnlocalizedItems () : array
    {
        return $this->filterItems(false);
    }



    /**
     * Filters all items to only include (un)localized ones
     *
     * @param bool $localized
     *
     * @return SearchItem[]
     */
    private function filterItems (bool $localized)
    {
        return array_filter(
            $this->searchItems,
            function (SearchItem $item) use ($localized)
            {
                return $localized === $item->isLocalized();
            }
        );
    }



    /**
     * @inheritdoc
     */
    public function getIterator ()
    {
        return new \ArrayIterator($this->searchItems);
    }



    /**
     * Filters the item list for the given classes.
     * If no class is given, all items are returned.
     *
     * @param array $itemClasses
     *
     * @return SearchItemList
     * @throws UnknownItemException
     */
    public function filterByClass (array $itemClasses) : SearchItemList
    {
        if (empty($itemClasses))
        {
            return $this;
        }

        $filtered = [];

        foreach ($itemClasses as $itemClass)
        {
            if (!isset($this->fqcnMap[$itemClass]))
            {
                throw new UnknownItemException($itemClass);
            }

            $item = $this->fqcnMap[$itemClass];

            $filtered[$item->getElasticsearchType()] = $item;
        }

        return new SearchItemList($filtered);
    }
}
