<?php

namespace Becklyn\SearchBundle\Metadata;

use Becklyn\SearchBundle\Metadata\SearchItem\SearchItemContentInterface;


class SearchItemFilter implements SearchItemContentInterface
{
    /**
     * @var string
     */
    private $accessorName;

    /**
     * @var string
     */
    private $filterName;


    /**
     * @var string
     */
    private $accessorType;



    /**
     * @param string $accessorName
     * @param string $filterName
     * @param string $accessorType
     */
    public function __construct (string $accessorName, string $filterName, string $accessorType)
    {
        if (!in_array($accessorType, [SearchItemField::ACCESSOR_TYPE_METHOD, SearchItemField::ACCESSOR_TYPE_PROPERTY], true))
        {
            throw new \InvalidArgumentException(sprintf(
                "Invalid accessor type: '%s'",
                $accessorType
            ));
        }

        $this->accessorName = $accessorName;
        $this->filterName = $filterName;
        $this->accessorType = $accessorType;
    }


    /**
     * @return string
     */
    public function getAccessorName () : string
    {
        return $this->accessorName;
    }




    /**
     * @return string
     */
    public function getFilterName () : string
    {
        return $this->filterName;
    }




    /**
     * @return string
     */
    public function getAccessorType () : string
    {
        return $this->accessorType;
    }



    /**
     * @return string
     */
    public function getElasticsearchFieldName () : string
    {
        return "filter-{$this->getFilterName()}";
    }
}
