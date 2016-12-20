<?php

namespace Becklyn\SearchBundle\Mapping;


/**
 * Annotation for marking a class as a searchable item
 *
 * @Annotation
 * @Target("CLASS")
 */
class Item
{
    /**
     * @var string|null
     */
    public $index = null;


    /**
     * @var string|null
     */
    public $loader = null;
}
