<?php

namespace Becklyn\SearchBundle\LanguageIntegration;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;


/**
 * Finds all classes in a given directory
 */
class ClassFinder
{
    /**
     * Finds all classes in the given directory
     *
     * @param array .<string, string> $rootNamespaces mapping of namespace prefix to root directory
     *
     * @return \ReflectionClass[] mapping of FQCN to reflection class (the key is the FQCN)
     */
    public function findClassesInDirectories (array $rootNamespaces) : array
    {
        $classMap = [];

        foreach ($rootNamespaces as $namespacePrefix => $rootDirectory)
        {
            $classMap = array_replace(
                $classMap,
                $this->findNamespacedClassesInRootDirectory($namespacePrefix, $rootDirectory)
            );
        }

        return $classMap;
    }



    /**
     * Finds all classes in the PSR-4 root directory with the given namespace prefix
     *
     * @param string $namespacePrefix
     * @param string $rootDirectory
     *
     * @return array.<string, \ReflectionClass> mapping of FQCN to reflection class
     */
    private function findNamespacedClassesInRootDirectory (string $namespacePrefix, string $rootDirectory) : array
    {
        if ("" !== $namespacePrefix && '\\' !== substr($namespacePrefix, -1))
        {
            throw new \InvalidArgumentException("The namespace prefix must either be empty, or end with a namespace separator.");
        }

        $finder = new Finder();

        $files = $finder
            ->in($rootDirectory)
            ->files()
            ->ignoreUnreadableDirs()
            ->name('*.php')
            ->exclude(["Tests", "tests", "Test", "test", "Skeleton", "skeleton"])
            ->contains("class ");

        $classMap = [];

        /** @var SplFileInfo $file */
        foreach ($files as $file)
        {
            $subNamespace = "" !== $file->getRelativePath()
                ? str_replace(DIRECTORY_SEPARATOR, '\\', $file->getRelativePath()) . '\\'
                : '';

            $fqcn = $namespacePrefix . $subNamespace . $file->getBasename(".{$file->getExtension()}");

            if (class_exists($fqcn))
            {
                $classMap[$fqcn] = new \ReflectionClass($fqcn);
            }
        }

        return $classMap;
    }
}
