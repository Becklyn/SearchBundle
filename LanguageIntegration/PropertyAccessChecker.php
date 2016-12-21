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

        $class = $property->getDeclaringClass();
        $camelCaseProperty = $this->camelize($property->getName());

        $possibleAccessors = [
            "get{$camelCaseProperty}",
            "is{$camelCaseProperty}",
            "has{$camelCaseProperty}",
        ];

        foreach ($possibleAccessors as $method)
        {
            if ($class->hasMethod($method) && $class->getMethod($method)->isPublic())
            {
                return true;
            }
        }

        return false;
    }


    /**
     * Transforms the string to camel case
     *
     * @param string $string
     *
     * @return string
     */
    private function camelize ($string) : string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
}
