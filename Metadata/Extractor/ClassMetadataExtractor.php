<?php

namespace Becklyn\SearchBundle\Metadata\Extractor;

use Becklyn\SearchBundle\Entity\SearchableEntityInterface;
use Becklyn\SearchBundle\Exception\InvalidSearchConfigurationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Becklyn\SearchBundle\Entity\LocalizedSearchableEntityInterface;
use Becklyn\SearchBundle\LanguageIntegration\AccessiblePropertyCollector;
use Becklyn\SearchBundle\Mapping\Field;
use Becklyn\SearchBundle\Mapping\Item;
use Becklyn\SearchBundle\Metadata\SearchItem;
use Becklyn\SearchBundle\Metadata\SearchItemField;


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

        if ($class->implementsInterface(SearchableEntityInterface::class))
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
            $annotation->loader
        );

        // Collect all indexed properties
        foreach ($this->collectPropertyFields($class) as $field)
        {
            $item->addField($field);
        }

        // Collect all indexed methods, that may have no backing field (as in "are computed")
        foreach ($this->collectEntityMethods($class) as $field)
        {
            $item->addField($field);
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

        foreach ($this->propertyCollector->getProperties($class) as $property)
        {
            /** @var Field $propertyAnnotation */
            $propertyAnnotation = $this->reader->getPropertyAnnotation($property, Field::class);

            // The property isn't meant to be indexed for searching
            if (null === $propertyAnnotation)
            {
                continue;
            }

            $propertyMetadata = new SearchItemField(
                $property->getName(),
                SearchItemField::ACCESSOR_TYPE_PROPERTY,
                $propertyAnnotation->weight,
                $propertyAnnotation->numberOfFragmentation
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
    private function collectEntityMethods (\ReflectionClass $class)
    {
        $methods = [];

        foreach ($this->propertyCollector->getMethods($class) as $method)
        {
            /** @var Field $propertyAnnotation */
            $propertyAnnotation = $this->reader->getMethodAnnotation($method, Field::class);

            // The method isn't meant to be indexed for searching
            if (null === $propertyAnnotation)
            {
                continue;
            }

            $propertyMetadata = new SearchItemField(
                $method->getName(),
                SearchItemField::ACCESSOR_TYPE_METHOD,
                $propertyAnnotation->weight,
                $propertyAnnotation->numberOfFragmentation
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
