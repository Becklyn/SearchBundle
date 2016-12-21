<?php

namespace Becklyn\SearchBundle\Exception;

class UnknownItemException extends SearchException
{
    /**
     * @inheritdoc
     */
    public function __construct (string $itemClass, \Exception $previous = null)
    {
        $message = sprintf(
            "Can't search item with item class '%s' as it is not indexed.",
            $itemClass
        );

        parent::__construct($message, $previous);
    }
}
