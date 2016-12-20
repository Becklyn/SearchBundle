<?php

namespace Becklyn\SearchBundle\Exception;


class InvalidEntityLoaderException extends SearchException
{
    /**
     * @inheritdoc
     */
    public function __construct (string $loader, \Exception $previous = null)
    {
        $message = sprintf(
            "Invalid loader '%s'. A loader must use the format 'service.name:method'.",
            $loader
        );

        parent::__construct($message, $previous);
    }
}
