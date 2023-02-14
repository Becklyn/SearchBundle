<?php

namespace Becklyn\SearchBundle\Metadata\Extractor;

use Becklyn\SearchBundle\Entity\SearchableEntityInterface;
use Becklyn\SearchBundle\Exception\InvalidSearchConfigurationException;
use Becklyn\SearchBundle\Mapping\Filter;
use Becklyn\SearchBundle\Metadata\SearchItemFilter;
use Becklyn\SearchBundle\Entity\LocalizedSearchableEntityInterface;
use Becklyn\SearchBundle\LanguageIntegration\AccessiblePropertyCollector;
use Becklyn\SearchBundle\Mapping\Field;
use Becklyn\SearchBundle\Mapping\Item;
use Becklyn\SearchBundle\Metadata\SearchItem;
use Becklyn\SearchBundle\Metadata\SearchItemField;
use Doctrine\Common\Annotations\AnnotationReader;


/**
 * Extracts the metadata from a class and generates search items
 */
class ClassMetadataExtractor
{
    /**
     * @var AnnotationReader
     */
    private $reader;


    /**
     * @var AccessiblePropertyCollector
     */
    private $propertyCollector;


    /**
     * @param AccessiblePropertyCollector $propertyCollector
     */
    public function __construct (AccessiblePropertyCollector $propertyCollector)
    {
        $this->reader = new AnnotationReader();
        $this->propertyCollector = $propertyCollector;
    }



    /**
     * Regenerates the search item for the given class
     *
     * @param \ReflectionClass $class
     *
     * @return SearchItem|null
     * @throws InvalidSearchConfigurationException
     */
    public function generateSearchItem (\ReflectionClass $class)
    {
        // don't support anonymous or abstract classes
        if ($class->isAbstract() || $class->isAnonymous())
        {
            return null;
        }

        /** @var Item $annotation */
        $annotation = $this->reader->getClassAnnotation($class, Item::class);

        // this class is not enabled for searching
        if (null === $annotation)
        {
            return null;
        }

        if (!$class->implementsInterface(SearchableEntityInterface::class))
        {
            throw new InvalidSearchConfigurationException(sprintf(
                "The for search annotated class '%s' must implemented the %s.",
                $class->getName(),
                SearchableEntityInterface::class
            ));
        }

        $item = new SearchItem(
            $class->getName(),
            $annotation->index ?: $this->generateElasticsearchTypeName($class),
            $class->implementsInterface(LocalizedSearchableEntityInterface::class),
            $annotation->loader,
            $annotation->autoIndex
        );

        // Collect all indexed properties
        foreach ($this->collectPropertyFields($class) as $field)
        {
            $item->addField($field);
        }

        // Collect all indexed methods, that may have no backing field (as in "are computed")
        foreach ($this->collectMethodFields($class) as $field)
        {
            $item->addField($field);
        }

        // Collect all property filters
        foreach ($this->collectPropertyFilters($class) as $filter)
        {
            $item->addFilter($filter);
        }

        // Collect all methods
        foreach ($this->collectMethodFilters($class) as $filter)
        {
            $item->addFilter($filter);
        }

        return $item;
    }



    /**
     * Collects all indexable entity properties
     *
     * @param \ReflectionClass $class
     *
     * @return SearchItemField[]
     */
    private function collectPropertyFields (\ReflectionClass $class)
    {
        $properties = [];

        foreach ($this->propertyCollector->getProperties($class, Field::class) as $annotatedProperty)
        {
            $property = $annotatedProperty->getProperty();
            /** @var Field $annotation */
            $annotation = $annotatedProperty->getAnnotation();

            $propertyMetadata = new SearchItemField(
                $property->getName(),
                SearchItemField::ACCESSOR_TYPE_PROPERTY,
                $annotation->weight,
                $annotation->format,
                $annotation->fragments
            );

            $properties[] = $propertyMetadata;
        }

        return $properties;
    }



    /**
     * Collects all indexable entity methods
     *
     * @param \ReflectionClass $class
     *
     * @return SearchItemField[]
     */
    private function collectMethodFields (\ReflectionClass $class)
    {
        $methods = [];

        foreach ($this->propertyCollector->getMethods($class, Field::class) as $annotatedMethod)
        {
            $method = $annotatedMethod->getMethod();
            /** @var Field $annotation */
            $annotation = $annotatedMethod->getAnnotation();

            $propertyMetadata = new SearchItemField(
                $method->getName(),
                SearchItemField::ACCESSOR_TYPE_METHOD,
                $annotation->weight,
                $annotation->format,
                $annotation->fragments
            );

            $methods[] = $propertyMetadata;
        }

        return $methods;
    }



    /**
     * Collects all indexable entity property filters
     *
     * @param \ReflectionClass $class
     *
     * @return SearchItemFilter[]
     */
    private function collectPropertyFilters (\ReflectionClass $class)
    {
        $properties = [];

        foreach ($this->propertyCollector->getProperties($class, Filter::class) as $annotatedProperty)
        {
            $property = $annotatedProperty->getProperty();
            /** @var Filter $annotation */
            $annotation = $annotatedProperty->getAnnotation();

            $propertyMetadata = new SearchItemFilter(
                $property->getName(),
                $annotation->name,
                SearchItemField::ACCESSOR_TYPE_PROPERTY
            );

            $properties[] = $propertyMetadata;
        }

        return $properties;
    }



    /**
     * Collects all indexable entity method filters
     *
     * @param \ReflectionClass $class
     *
     * @return SearchItemFilter[]
     */
    private function collectMethodFilters (\ReflectionClass $class)
    {
        $methods = [];

        foreach ($this->propertyCollector->getMethods($class, Filter::class) as $annotatedMethod)
        {
            $method = $annotatedMethod->getMethod();
            /** @var Filter $annotation */
            $annotation = $annotatedMethod->getAnnotation();

            $propertyMetadata = new SearchItemFilter(
                $method->getName(),
                $annotation->name,
                SearchItemField::ACCESSOR_TYPE_METHOD
            );

            $methods[] = $propertyMetadata;
        }

        return $methods;
    }



    /**
     * Creates the index name based on the Entity's class name
     *
     * @param \ReflectionClass $class
     *
     * @return string
     */
    private function generateElasticsearchTypeName (\ReflectionClass $class) : string
    {
        $name = strtolower($class->getName());

        // replace \ with -
        $name = str_replace('\\', '-', $name);

        // convert camelCase to snake_case
        $name = preg_replace('/([a-z])([A-Z])/', '$1_$2', $name);

        return $name;
    }
}
