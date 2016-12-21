<?php

namespace Becklyn\SearchBundle\FormatProcessor;


/**
 * Interface for marking content renderers
 */
interface TextFormatProcessor
{
    /**
     * Processes the given text
     *
     * @param string $text
     *
     * @return string
     */
    public function process ($text) : string;
}
