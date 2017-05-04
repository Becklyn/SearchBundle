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


    /**
     * Determines whether the searchable item should be indexed automatically
     * whenever it's persisted or updated in Doctrine
     *
     * @var bool
     */
    public $autoIndex = true;
}
