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
     * @var string
     */
    public $index = null;


    /**
     * @var string
     */
    public $loader = null;
}
