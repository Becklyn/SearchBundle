<?php

namespace Becklyn\SearchBundle\Search\Result;

class SearchResult implements \IteratorAggregate, \Countable
{
    /**
     * @var EntitySearchHits[]
     */
    private $resultLists = [];


    /**
     * @var int
     */
    private $totalCount = 0;


    /**
     * @var float
     */
    private $maxScore = 0;



    /**
     * @param EntitySearchHits[] $resultLists
     */
    public function __construct (array $resultLists)
    {
        foreach ($resultLists as $resultList)
        {
            $this->resultLists[$resultList->getEntityClass()] = $resultList;
            $this->totalCount += count($resultList);
            $this->maxScore = max($this->maxScore, $resultList->getMaxScore());
        }
    }



    /**
     * Returns a list of all entity search hits lists
     *
     * @return EntitySearchHits[]
     */
    public function getEntityResultLists () : array
    {
        return $this->resultLists;
    }



    /**
     * @param string $fqcn
     *
     * @return EntitySearchHits|null
     */
    public function getResults (string $fqcn)
    {
        return $this->resultLists[$fqcn] ?? null;
    }



    /**
     * @return int
     */
    public function getTotalCount () : int
    {
        return $this->totalCount;
    }



    /**
     * @return float
     */
    public function getMaxScore () : float
    {
        return $this->maxScore;
    }



    /**
     * @inheritdoc
     */
    public function count ()
    {
        return count($this->resultLists);
    }



    /**
     * @inheritdoc
     */
    public function getIterator ()
    {
        return new \ArrayIterator($this->resultLists);
    }
}
