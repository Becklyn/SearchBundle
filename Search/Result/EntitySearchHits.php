<?php

namespace Becklyn\SearchBundle\Search\Result;

use Becklyn\Interfaces\LanguageInterface;


/**
 * A list of all search hits for a specific entity
 */
class EntitySearchHits implements \IteratorAggregate, \Countable
{
    /**
     * @var string
     */
    private $entityClass;


    /**
     * @var SearchHit[]
     */
    private $hits;


    /**
     * @var float
     */
    private $maxScore = null;



    /**
     * @param string      $entityClass
     * @param SearchHit[] $hits
     */
    public function __construct (string $entityClass, array $hits)
    {
        $this->entityClass = $entityClass;
        $this->hits = $hits;

        // sort hits by score
        usort(
            $this->hits,
            function (SearchHit $left, SearchHit $right)
            {
                return $right->getScore() - $left->getScore();
            }
        );
    }



    /**
     * @return float|int
     */
    public function getMaxScore ()
    {
        if (null === $this->maxScore)
        {
            $this->maxScore = 0;

            if (0 < count($this->hits))
            {
                // the results are ordered, the best score is the first one
                $firstResult = reset($this->hits);
                $this->maxScore = $firstResult->getScore();
            }
        }

        return $this->maxScore;
    }



    /**
     * @return SearchHit[]
     */
    public function getHits () : array
    {
        return $this->hits;
    }



    /**
     * @return string
     */
    public function getEntityClass () : string
    {
        return $this->entityClass;
    }



    /**
     * @inheritdoc
     */
    public function count ()
    {
        return count($this->hits);
    }



    /**
     * @inheritdoc
     */
    public function getIterator ()
    {
        return new \ArrayIterator($this->hits);
    }
}
