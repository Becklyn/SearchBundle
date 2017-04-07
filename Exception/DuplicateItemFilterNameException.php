<?php

namespace Becklyn\SearchBundle\Exception;


/**
 * Exception for duplicate registration of a filter
 */
class DuplicateItemFilterNameException extends SearchException
{
    /**
     * @inheritdoc
     */
    public function __construct (string $filterName, \Exception $previous = null)
    {
        $message = sprintf(
            "There already is a filter with name %s.",
            $filterName
        );

        parent::__construct($message, $previous);
    }
}
