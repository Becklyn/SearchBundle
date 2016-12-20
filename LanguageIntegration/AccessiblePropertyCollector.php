<?php

namespace Becklyn\SearchBundle\LanguageIntegration;


/**
 * Language integration, that extracts accessible properties / public methods from a given class.
 */
class AccessiblePropertyCollector
{
    /**
     * @var PropertyAccessChecker
     */
    private $propertyAccessChecker;



    public function __construct ()
    {
        $this->propertyAccessChecker = new PropertyAccessChecker();
    }


    /**
     * Searches all possible properties defined in the complete class hierarchy that are accessible
     *
     * @param \ReflectionClass $class
     *
     * @return \ReflectionProperty[]
     */
    public function getProperties (\ReflectionClass $class) : array
    {
        $items = function (\ReflectionClass $class)
        {
            return $class->getProperties();
        };

        $candidates = $this->fetchCandidates($class, $items, []);

        return array_filter(
            $candidates,
            function (\ReflectionProperty $property)
            {
                return $this->propertyAccessChecker->isAccessible($property);
            }
        );
    }


    /**
     * Searches all possible methods defined in the complete class hierarchy that are accessible
     *
     * @param \ReflectionClass $class
     *
     * @return \ReflectionMethod[]
     */
    public function getMethods (\ReflectionClass $class) : array
    {
        $items = function (\ReflectionClass $class)
        {
            return $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        };

        $candidates = $this->fetchCandidates($class, $items, []);

        return array_filter(
            $candidates,
            function (\ReflectionMethod $method)
            {
                return 0 === $method->getNumberOfRequiredParameters();
            }
        );
    }



    /**
     * Recursively fetches all candidates
     *
     * @param \ReflectionClass                          $class
     * @param callable                                  $extractFields
     * @param \ReflectionProperty[]|\ReflectionMethod[] $candidates
     *
     * @return \ReflectionProperty[]|\ReflectionMethod[]
     */
    private function fetchCandidates (\ReflectionClass $class, callable $extractFields, array $candidates) : array
    {
        /** @var \ReflectionProperty[]|\ReflectionMethod[]  $fieldCandidates */
        $fieldCandidates = $extractFields($class);

        foreach ($fieldCandidates as $candidate)
        {
            if (!array_key_exists($candidate->getName(), $candidates))
            {
                $candidates[$candidate->getName()] = $candidate;
            }
        }

        if (false !== $parentClass = $class->getParentClass())
        {
            $candidates = $this->fetchCandidates($parentClass, $extractFields, $candidates);
        }

        return $candidates;
    }
}
