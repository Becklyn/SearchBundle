<?php

namespace Becklyn\SearchBundle\Metadata;

use Becklyn\SearchBundle\LanguageIntegration\ClassFinder;
use Becklyn\SearchBundle\Metadata\Extractor\ClassMetadataExtractor;


/**
 * (Re)Generates the metadata for the complete application
 */
class MetadataGenerator
{
    /**
     * @var ClassFinder
     */
    private $classFinder;


    /**
     * @var ClassMetadataExtractor
     */
    private $classMetadataExtractor;


    /**
     * @var Metadata
     */
    private $metadata;



    /**
     * @param ClassFinder            $classFinder
     * @param ClassMetadataExtractor $classMetadataExtractor
     * @param Metadata               $metadata
     */
    public function __construct (ClassFinder $classFinder, ClassMetadataExtractor $classMetadataExtractor, Metadata $metadata)
    {
        $this->classFinder = $classFinder;
        $this->classMetadataExtractor = $classMetadataExtractor;
        $this->metadata = $metadata;
    }



    /**
     * Rebuilds the metadata for all classes in the given directories
     *
     * @param array.<string,string> $rootNamespaces mapping of namespace prefix to root directory
     *
     * @return SearchItem[] the discovered search items (that were already added to the metadata)
     */
    public function rebuildMetadata (array $rootNamespaces)
    {
        $this->metadata->clear();
        $classes = $this->classFinder->findClassesInDirectories($rootNamespaces);

        $discoveredSearchItems = [];

        foreach ($classes as $class)
        {
            $searchItem = $this->classMetadataExtractor->generateSearchItem($class);

            if (null === $searchItem)
            {
                continue;
            }

            $this->metadata->add($searchItem);
            $discoveredSearchItems[] = $searchItem;
        }

        return $discoveredSearchItems;
    }
}
