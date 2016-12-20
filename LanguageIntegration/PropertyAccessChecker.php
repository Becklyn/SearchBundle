<?php

namespace Becklyn\SearchBundle\LanguageIntegration;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;


/**
 * Detects, whether a given property is accessible
 * (either directly or via a getter)
 */
class PropertyAccessChecker
{
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;



    public function __construct ()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }



    /**
     * Returns whether the property is somehow accessible
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isAccessible (\ReflectionProperty $property) : bool
    {
        // if the property is directly accessible -> fast return
        if ($property->isPublic())
        {
            return true;
        }

        // try to find a getter for the property
        $emptyObject = $property->getDeclaringClass()->newInstanceWithoutConstructor();
        return $this->propertyAccessor->isReadable($emptyObject, $property->getName());
    }
}
