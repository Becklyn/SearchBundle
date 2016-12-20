<?php

namespace Becklyn\SearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class IndexCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('becklyn:search:index')
            ->setDescription('(Re-)Indexes all app entities into ElasticSearch');
    }

    /**
     * @inheritdoc
     */
    protected function execute (InputInterface $input, OutputInterface $io)
    {
        $io = new SymfonyStyle($input, $io);

        $io->title("Regenerating ElasticSearch Indexes");

        $this->refreshMetaData($io);

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
     * Refreshes the meta data
     *
     * @param SymfonyStyle $io
     */
    private function refreshMetaData (SymfonyStyle $io)
    {
        $metadata = $this->getContainer()->get("becklyn.search.metadata");
        $metadataGenerator = $this->getContainer()->get("becklyn.search.metadata.generator");
        $kernel = $this->getContainer()->get("kernel");

        $io->section("Refreshing the metadata");

        $metadata->clear();
        $io->text("Clear metadata");

        foreach ($kernel->getBundles() as $bundle)
        {
            $bundleNamespacePrefix = "{$bundle->getName()}\\";

            $searchItems = $metadataGenerator->rebuildMetadata([
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
}
