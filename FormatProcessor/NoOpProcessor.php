<?php

namespace Becklyn\SearchBundle\FormatProcessor;


/**
 * A text processor that doesn't perform any further action
 */
class NoOpProcessor implements TextFormatProcessor
{
    /**
     * @inheritdoc
     */
    public function process ($text) : string
    {
        return $text;
    }
}
