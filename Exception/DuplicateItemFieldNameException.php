<?php

namespace Becklyn\SearchBundle\Exception;


/**
 * Exception for duplicate registration of accessor type + name combinations
 */
class DuplicateItemFieldNameException extends SearchException
{
    /**
     * @inheritdoc
     */
    public function __construct (string $field, string $class, \Exception $previous = null)
    {
        $message = sprintf(
            "There already is a field with name %s in class %s.",
            $field,
            $class
        );

        parent::__construct($message, $previous);
    }
}
