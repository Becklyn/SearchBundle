<?php

namespace Becklyn\SearchBundle\Metadata;


use Becklyn\SearchBundle\Exception\DuplicateItemFieldNameException;
use Becklyn\SearchBundle\Exception\DuplicateItemFilterNameException;


/**
 * A searchable entity
 */
class SearchItem
{
    /**
     * @var string
     */
    private $fqcn;


    /**
     * @var string
     */
    private $elasticsearchType;


    /**
     * @var SearchItemField[]
     */
    private $fields = [];


    /**
     * @var SearchItemFilter[]
     */
    private $filters = [];


    /**
     * @var bool
     */
    private $localized;


    /**
     * @var string|null
     */
    private $loader;


    /**
     * @var bool
     */
    private $autoIndex = true;



    /**
     * @param string      $fqcn
     * @param string      $elasticsearchType
     * @param bool        $localized
     * @param string|null $loader
     * @param bool        $autoIndex
     */
    public function __construct (string $fqcn, string $elasticsearchType, bool $localized, string $loader = null, bool $autoIndex = true)
    {
        $this->fqcn = $fqcn;
        $this->elasticsearchType = $elasticsearchType;
        $this->localized = $localized;
        $this->loader = $loader;
        $this->autoIndex = $autoIndex;
    }



    /**
     * @return string
     */
    public function getFqcn () : string
    {
        return $this->fqcn;
    }



    /**
     * @return string
     */
    public function getElasticsearchType () : string
    {
        return $this->elasticsearchType;
    }


    /**
     * @return SearchItemField[]
     */
    public function getFields () : array
    {
        return $this->fields;
    }


    /**
     * @return SearchItemFilter[]
     */
    public function getFilters () : array
    {
        return $this->filters;
    }



    /**
     * @return bool
     */
    public function isLocalized () : bool
    {
        return $this->localized;
    }



    /**
     * @return null|string
     */
    public function getLoader ()
    {
        return $this->loader;
    }



    /**
     * @param SearchItemField $field
     *
     * @throws DuplicateItemFieldNameException
     */
    public function addField (SearchItemField $field)
    {
        if (isset($this->fields[$field->getElasticsearchFieldName()]))
        {
            throw new DuplicateItemFieldNameException($field->getName(), $field->getAccessorType(), $this->getFqcn());
        }

        $this->fields[$field->getElasticsearchFieldName()] = $field;
    }



    /**
     * @param SearchItemFilter $filter
     *
     * @throws DuplicateItemFilterNameException
     */
    public function addFilter (SearchItemFilter $filter)
    {
        if (isset($this->filters[$filter->getElasticsearchFieldName()]))
        {
            throw new DuplicateItemFilterNameException($filter->getFilterName(), $this->getFqcn());
        }

        $this->filters[$filter->getElasticsearchFieldName()] = $filter;
    }


    /**
     * @return bool
     */
    public function isAutoIndexed () : bool
    {
        return $this->autoIndex;
    }
}
