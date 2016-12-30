<?php

namespace Becklyn\SearchBundle\Search\Result;


class SearchResultBuilder
{
    /**
     * @var SearchHit[][]
     */
    private $classMapping = [];



    public function addHit (SearchHit $hit)
    {
        $entity = $hit->getEntity();
        $id = $entity->getId();
        $entityClass = get_class($entity);

        if (isset($this->classMapping[$entityClass][$id]))
        {
            $this->classMapping[$entityClass][$id]->mergeHit($hit);
        }
        else
        {
            $this->classMapping[$entityClass][$id] = $hit;
        }
    }



    /**
     * Builds the search result
     *
     * @return SearchResult The list of all search hits, grouped by actual entity
     */
    public function getSearchResult () : SearchResult
    {
        $entitySearchHits = [];

        foreach ($this->classMapping as $fqcn => $hits)
        {
            $entitySearchHits[] = new EntitySearchHits($fqcn, $hits);
        }

        return new SearchResult($entitySearchHits);
    }
}
