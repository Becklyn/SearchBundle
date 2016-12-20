<?php

namespace Becklyn\SearchBundle\Metadata;


/**
 * An indexed field of a searchable entity
 */
class SearchItemField
{
    const ACCESSOR_TYPE_PROPERTY = "property";
    const ACCESSOR_TYPE_METHOD = "method";
    const FRAGMENTATION_DEFAULT = 5;


    /**
     * @var string
     */
    private $name;


    /**
     * @var int
     */
    private $weight;


    /**
     * @var int
     */
    private $numberOfFragments;


    /**
     * @var string
     */
    private $accessorType;



    /**
     * @param string   $name
     * @param int      $weight
     * @param string   $accessorType
     * @param int|null $numberOfFragments
     */
    public function __construct (string $name, string $accessorType, int $weight, int $numberOfFragments = null)
    {
        if (!in_array($accessorType, [self::ACCESSOR_TYPE_METHOD, self::ACCESSOR_TYPE_PROPERTY], true))
        {
            throw new \InvalidArgumentException(sprintf(
                "Invalid accessor type: '%s'",
                $accessorType
            ));
        }

        $this->name = $name;
        $this->accessorType = $accessorType;
        $this->weight = $weight;
        $this->numberOfFragments = $numberOfFragments ?? self::FRAGMENTATION_DEFAULT;
    }



    /**
     * @return string
     */
    public function getName () : string
    {
        return $this->name;
    }



    /**
     * @return string
     */
    public function getElasticsearchFieldName () : string
    {
        return "{$this->accessorType}-{$this->getName()}";
    }



    /**
     * @return int
     */
    public function getWeight () : int
    {
        return $this->weight;
    }



    /**
     * @return int
     */
    public function getNumberOfFragments () : int
    {
        return $this->numberOfFragments;
    }



    /**
     * @return string
     */
    public function getAccessorType () : string
    {
        return $this->accessorType;
    }
}
