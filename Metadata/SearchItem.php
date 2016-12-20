<?php

namespace Becklyn\SearchBundle\Metadata;


use Becklyn\SearchBundle\Exception\DuplicateItemFieldNameException;


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
     * @var bool
     */
    private $translated;


    /**
     * @var string|null
     */
    private $loader;



    /**
     * @param string      $fqcn
     * @param string      $elasticsearchType
     * @param bool        $translated
     * @param string|null $loader
     */
    public function __construct (string $fqcn, string $elasticsearchType, bool $translated, string $loader = null)
    {
        $this->fqcn = $fqcn;
        $this->elasticsearchType = $elasticsearchType;
        $this->translated = $translated;
        $this->loader = $loader;
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
     * @return bool
     */
    public function isTranslated () : bool
    {
        return $this->translated;
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
}
