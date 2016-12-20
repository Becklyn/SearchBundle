<?php

namespace Becklyn\SearchBundle\Exception;


/**
 * Base class for all exceptions in the search bundle
 */
class SearchException extends \Exception
{
    /**
     * @inheritDoc
     */
    public function __construct (string $message, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
