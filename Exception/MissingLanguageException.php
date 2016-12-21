<?php

namespace Becklyn\SearchBundle\Exception;

use Becklyn\SearchBundle\Metadata\SearchItem;


class MissingLanguageException extends SearchException
{
    /**
     * @param SearchItem[]    $items
     * @param \Exception|null $previous
     */
    public function __construct (array $items, \Exception $previous = null)
    {
        $message = sprintf(
            "Search items with classes '%s' require a language for searching, no language given.",
            implode(
                "', '",
                array_map(
                    function (SearchItem $item)
                    {
                        return $item->getFqcn();
                    },
                    $items
                )
            )
        );

        parent::__construct($message, $previous);
    }
}
