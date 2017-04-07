<?php

namespace Becklyn\SearchBundle\LanguageIntegration;

use Becklyn\SearchBundle\Exception\InvalidSearchConfigurationException;
use Becklyn\SearchBundle\LanguageIntegration\AnnotatedField\AnnotatedMethod;
use Becklyn\SearchBundle\LanguageIntegration\AnnotatedField\AnnotatedProperty;
use Becklyn\SearchBundle\Mapping\Field;
use Doctrine\Common\Annotations\AnnotationReader;


/**
 * Language integration, that extracts accessible properties / public methods from a given class.
 */
class AccessiblePropertyCollector
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @var PropertyAccessChecker
     */
    private $propertyAccessChecker;



    public function __construct ()
    {
        $this->reader = new AnnotationReader();
        $this->propertyAccessChecker = new PropertyAccessChecker();
    }



    /**
     * Searches all possible properties defined in the complete class hierarchy that are accessible
     *
     * @param \ReflectionClass $class
     * @param string           $annotationClass
     *
     * @return array|AnnotatedProperty[]
     * @throws InvalidSearchConfigurationException
     */
    public function getProperties (\ReflectionClass $class, string $annotationClass) : array
    {
        $items = function (\ReflectionClass $class)
        {
            return $class->getProperties();
        };

        $properties = $this->fetchCandidates($class, $items, []);
        $filtered = [];

        foreach ($properties as $property)
        {
            /** @var Field $annotation */
            $annotation = $this->reader->getPropertyAnnotation($property, $annotationClass);

            if (null === $annotation)
            {
                continue;
            }

            if (!$this->propertyAccessChecker->isAccessible($property))
            {
                throw new InvalidSearchConfigurationException(sprintf(
                    "Annotation found on property %s::$%s, but no way to access the property either directly or via is*(), has*() or get*() accessors.",
                    $class->getName(),
                    $property->getName()
                ));
            }

            $filtered[] = new AnnotatedProperty($property, $annotation);
        }

        return $filtered;
    }



    /**
     * Searches all possible methods defined in the complete class hierarchy that are accessible
     *
     * @param \ReflectionClass $class
     * @param string           $annotationClass
     *
     * @return array|AnnotatedMethod[]
     * @throws InvalidSearchConfigurationException
     */
    public function getMethods (\ReflectionClass $class, string $annotationClass) : array
    {
        $items = function (\ReflectionClass $class)
        {
            return $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        };

        $methods = $this->fetchCandidates($class, $items, []);
        $filtered = [];

        foreach ($methods as $method)
        {
            /** @var Field $annotation */
            $annotation = $this->reader->getMethodAnnotation($method, $annotationClass);

            if (null === $annotation)
            {
                continue;
            }

            if (0 !== $method->getNumberOfRequiredParameters())
            {
                throw new InvalidSearchConfigurationException(sprintf(
                    "Can't use method %s::$%s for search indexing, as the method has required parameters.",
                    $class->getName(),
                    $method->getName()
                ));
            }

            $filtered[] = new AnnotatedMethod($method, $annotation);
        }

        return $filtered;
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
