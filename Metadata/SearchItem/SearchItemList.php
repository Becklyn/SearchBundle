<?php

namespace Becklyn\SearchBundle\Metadata\SearchItem;

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
     * @param SearchItem[] $searchItems
     */
    public function __construct (array $searchItems)
    {
        $this->searchItems = $searchItems;
    }



    /**
     * Returns all localized items
     *
     * @return SearchItem[]
     */
    public function getAllLocalizedItems () : array
    {
        return $this->filterItems(true);
    }



    /**
     * Returns all unlocalized items
     *
     * @return SearchItem[]
     */
    public function getAllUnlocalizedItems () : array
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
}
