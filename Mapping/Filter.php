<?php

namespace Becklyn\SearchBundle\Mapping;

use Doctrine\Common\Annotations\Annotation\Required;


/**
 * Marks the property or method as a filter field for an searchable item
 *
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
class Filter
{
    public function __construct (array $args)
    {
        if (isset($args["value"]))
        {
            if (\is_string($args["value"]))
            {
                $this->name = $args["value"];
            }
            else
            {
                throw new \InvalidArgumentException(sprintf(
                    "Can't automatically use arg of type %s",
                    \gettype($args["value"])
                ));
            }
        }
    }

    /**
     * @Required()
     * @var string
     */
    public $name;
}
