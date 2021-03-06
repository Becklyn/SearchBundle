<?php

namespace Becklyn\SearchBundle\Mapping;


/**
 * Marks the property or method as a field of an searchable item
 *
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
class Field
{
    /**
     * @var integer
     */
    public $weight = 1;


    /**
     * @var integer|null
     */
    public $fragments = null;


    /**
     * @var string
     */
    public $format = "plain";
}
