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
     * @var int
     */
    public $weight = 1;


    /**
     * @var int|null
     */
    public $numberOfFragmentation = null;
}
