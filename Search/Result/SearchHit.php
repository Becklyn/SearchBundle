<?php

namespace Becklyn\SearchBundle\Search\Result;

use Becklyn\SearchBundle\Entity\SearchableEntityInterface;


/**
 * A single hit for a search request
 */
class SearchHit
{
    /**
     * @var SearchableEntityInterface
     */
    private $entity;


    /**
     * @var float
     */
    private $score;


    /**
     * @var array
     */
    private $highlights;



    /**
     * @param SearchableEntityInterface $entity
     * @param float                     $score
     * @param array                     $highlights
     */
    public function __construct (SearchableEntityInterface $entity, float $score, array $highlights)
    {
        $this->entity = $entity;
        $this->score = $score;
        $this->highlights = $highlights;
    }



    /**
     * @return SearchableEntityInterface
     */
    public function getEntity () : SearchableEntityInterface
    {
        return $this->entity;
    }



    /**
     * @return float
     */
    public function getScore () : float
    {
        return $this->score;
    }



    /**
     * @return array
     */
    public function getAllHighlights () : array
    {
        if (empty($this->highlights))
        {
            return [];
        }

        return array_merge(...array_values($this->highlights));
    }



    /**
     * Merges the hit with another hit
     *
     * @param SearchHit $hit
     */
    public function mergeHit (SearchHit $hit)
    {
        if ($hit->getEntity() !== $this->getEntity())
        {
            throw new \InvalidArgumentException("Can't merge with a hit of another entity.");
        }

        // merge highlights
        foreach ($hit->highlights as $field => $highlights)
        {
            if (!isset($this->highlights[$field]))
            {
                $this->highlights[$field] = $highlights;
                continue;
            }

            foreach ($highlights as $highlight)
            {
                $this->highlights[$field][] = $highlight;
            }
        }

        $this->score += $hit->score / 2;
    }
}
