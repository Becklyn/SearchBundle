<?php

namespace Becklyn\SearchBundle\Command;

use Becklyn\SearchBundle\Index\IndexMapping;
use Becklyn\SearchBundle\Loader\EntityLoader;
use Becklyn\SearchBundle\Metadata\Metadata;
use Becklyn\SearchBundle\Metadata\MetadataGenerator;
use Becklyn\SearchBundle\Search\SearchIndexer;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;


class IndexCommand extends Command
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
     * @var IndexMapping
     */
    private $indexMapping;


    /**
     * @var SearchIndexer
     */
    private $indexer;


    /**
     * @var EntityLoader
     */
    private $entityLoader;


    /**
     * @var EntityManager
     */
    private $entityManager;



    /**
     * @param Metadata          $metadata
     * @param MetadataGenerator $metadataGenerator
     * @param KernelInterface   $kernel
     * @param IndexMapping      $indexMapping
     * @param SearchIndexer     $indexer
     * @param EntityLoader      $entityLoader
     * @param Registry          $doctrine
     *
     * @internal param MetadataGenerator $generator
     */
    public function __construct (
        Metadata $metadata,
        MetadataGenerator $metadataGenerator,
        KernelInterface $kernel,
        IndexMapping $indexMapping,
        SearchIndexer $indexer,
        EntityLoader $entityLoader,
        Registry $doctrine
    )
    {
        parent::__construct("becklyn:search:index");

        $this->metadata = $metadata;
        $this->metadataGenerator = $metadataGenerator;
        $this->kernel = $kernel;
        $this->indexMapping = $indexMapping;
        $this->indexer = $indexer;
        $this->entityLoader = $entityLoader;
        $this->entityManager = $doctrine->getManager();
    }


    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('(Re-)Indexes all app entities into ElasticSearch');
    }

    /**
     * @inheritdoc
     */
    protected function execute (InputInterface $input, OutputInterface $io)
    {
        $io = new SymfonyStyle($input, $io);

        $io->title("Regenerating ElasticSearch Indexes");

        $this->clearMetaData($io);
        $this->refreshMetaData($io);
        $this->regenerateIndex($io);
        $this->indexAllEntities($io);

        $io->success("All done.");
    }



    /**
     * Prints a "done" message for a single step
     *
     * @param $io
     */
    private function stepDone (SymfonyStyle $io)
    {
        $io->writeln("<fg=green>done.</>");
        $io->newLine(2);
    }



    /**
     * Clears all existing metadata
     *
     * @param SymfonyStyle $io
     */
    private function clearMetaData (SymfonyStyle $io)
    {
        $io->section("Clear existing metadata");

        $this->metadata->clear();

        $this->stepDone($io);
    }


    /**
     * Refreshes the meta data
     *
     * @param SymfonyStyle $io
     */
    private function refreshMetaData (SymfonyStyle $io)
    {
        $io->section("Refreshing the metadata");

        foreach ($this->kernel->getBundles() as $bundle)
        {
            $bundleNamespacePrefix = "{$bundle->getNamespace()}\\";

            $searchItems = $this->metadataGenerator->rebuildMetadata([
                $bundleNamespacePrefix => $bundle->getPath(),
            ]);

            if (!empty($searchItems))
            {
                $io->writeln(sprintf(
                    "<fg=blue>%s</>",
                    $bundle->getName()
                ));

                foreach ($searchItems as $item)
                {
                    $io->writeln("  {$item->getFqcn()}");
                }

                $io->newLine();
            }
        }

        $this->stepDone($io);
    }



    /**
     * Regenerates all indexes
     *
     * @param SymfonyStyle $io
     */
    private function regenerateIndex (SymfonyStyle $io)
    {
        $io->section("Refreshing the index mapping");
        $this->indexMapping->regenerateIndex();

        $this->stepDone($io);
    }



    /**
     * Reindexes all entities
     *
     * @param SymfonyStyle $io
     */
    private function indexAllEntities (SymfonyStyle $io)
    {
        $io->section("Indexing all entities");

        foreach ($this->metadata->getAllItems() as $item)
        {
            $io->writeln(sprintf(
                "<fg=blue>%s</>",
                $item->getFqcn()
            ));

            $entities = $this->entityLoader->loadEntities($item, EntityLoader::LOAD_ALL_ENTITIES);
            $count = count($entities);

            if (0 === $count)
            {
                $io->writeln("  No entities found.");
            }
            else
            {
                $this->indexer->bulkIndex($entities->getAllResults());

                $io->writeln(sprintf(
                    "  <fg=green>%d %s indexed</>",
                    $count,
                    1 === $count ? "entity" : "entities"
                ));

                $entities = null;
                $this->entityManager->clear();
            }

            $io->newLine();
        }

        $this->stepDone($io);
    }
}
