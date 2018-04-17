<?php declare(strict_types=1);

namespace Becklyn\SearchBundle\Metadata;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;


class MetadataFactory
{
    /**
     * @var Metadata
     */
    private $metadata;


    /**
     * @var MetadataGenerator
     */
    private $metadataGenerator;


    /**
     * @var KernelInterface
     */
    private $kernel;


    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     *
     * @param Metadata          $metadata
     * @param MetadataGenerator $metadataGenerator
     * @param KernelInterface   $kernel
     * @param LoggerInterface   $logger
     */
    public function __construct (Metadata $metadata, MetadataGenerator $metadataGenerator, KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->metadata = $metadata;
        $this->metadataGenerator = $metadataGenerator;
        $this->kernel = $kernel;
        $this->logger = $logger;
    }


    /**
     * Returns the metadata instance
     *
     * @return Metadata
     */
    public function getMetadata ()
    {
        if (!$this->metadata->isInitialized())
        {
            $this->rebuildCompleteMetadata();
            $this->logger->warning("Rebuilding the complete metadata in production.");
        }

        return $this->metadata;
    }


    /**
     * Rebuilds the complete metadata
     */
    private function rebuildCompleteMetadata ()
    {
        $this->metadata->clear();

        foreach ($this->kernel->getBundles() as $bundle)
        {
            $bundleNamespacePrefix = "{$bundle->getName()}\\";

            $this->metadataGenerator->rebuildMetadata([
                $bundleNamespacePrefix => $bundle->getPath(),
            ]);
        }
    }
}
