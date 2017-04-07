<?php

namespace Becklyn\SearchBundle\Command;

use Becklyn\SearchBundle\Metadata\Metadata;
use Becklyn\SearchBundle\Metadata\MetadataGenerator;
use Becklyn\SearchBundle\Metadata\SearchItem;
use Becklyn\SearchBundle\Metadata\SearchItemField;
use Becklyn\SearchBundle\Metadata\SearchItemFilter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;


class MetadataCommand extends ContainerAwareCommand
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
     * @param Metadata          $metadata
     * @param MetadataGenerator $metadataGenerator
     * @param KernelInterface   $kernel
     */
    public function __construct (Metadata $metadata, MetadataGenerator $metadataGenerator, KernelInterface $kernel)
    {
        parent::__construct("becklyn:search:metadata");

        $this->metadata = $metadata;
        $this->metadataGenerator = $metadataGenerator;
        $this->kernel = $kernel;

        $this
            ->addOption(
                "refresh",
                null,
                InputOption::VALUE_NONE,
                "Refresh metadata before printing the cached data."
            );
    }



    /**
     * @inheritDoc
     */
    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Search Metadata");

        if ($input->getOption("refresh"))
        {
            $this->refreshMetadata($io);
        }

        foreach ($this->loadItems() as $item)
        {
            $io->section($item->getFqcn());

            if ($item->isLocalized())
            {
                $io->writeln('<fg=blue>(localized)</>');
                $io->newLine();
            }

            $io->text("<fg=yellow>Fields</>");

            $io->table(
                [
                    "Name",
                    "Type",
                    "Weight",
                    "Format",
                    "Fragments",
                    "ES Name",
                ],
                array_map(
                    function (SearchItemField $field)
                    {
                        return [
                            '<fg=yellow>' . $field->getName() . '</>',
                            $field->getAccessorType(),
                            $field->getWeight(),
                            $field->getFormat(),
                            $field->getNumberOfFragments(),
                            $field->getElasticsearchFieldName(),
                        ];
                    },
                    $item->getFields()
                )
            );

            $filters = $item->getFilters();

            if (!empty($filters))
            {
                $io->newLine();
                $io->text("<fg=yellow>Filters</>");

                $io->table(
                    [
                        "Name",
                        "Type",
                        "ES Name",
                    ],
                    array_map(
                        function (SearchItemFilter $filter)
                        {
                            return [
                                '<fg=yellow>' . $filter->getFilterName() . '</>',
                                $filter->getAccessorType(),
                                $filter->getElasticsearchFieldName(),
                            ];
                        },
                        $filters
                    )
                );
            }
        }

        $io->success("Done.");
    }



    /**
     * Refreshes the metadata
     *
     * @param SymfonyStyle $io
     */
    private function refreshMetadata (SymfonyStyle $io)
    {
        $io->section("Refreshing the metadata");

        $io->text("Remove existing metadata");
        $this->metadata->clear();

        $io->text("Importing new metadata");
        foreach ($this->kernel->getBundles() as $bundle)
        {
            $io->writeln("  - <fg=blue>{$bundle->getName()}</>");
            $bundleNamespacePrefix = "{$bundle->getName()}\\";

            $this->metadataGenerator->rebuildMetadata([
                $bundleNamespacePrefix => $bundle->getPath(),
            ]);
        }

        $io->newLine(2);
    }



    /**
     * @return SearchItem[]
     */
    private function loadItems () : array
    {
        $metadata = $this->getContainer()->get("becklyn.search.metadata");

        /** @var SearchItem[] $items */
        $items = iterator_to_array($metadata->getAllItems());

        usort(
            $items,
            function (SearchItem $left, SearchItem $right)
            {
                return strnatcasecmp($left->getFqcn(), $right->getFqcn());
            }
        );

        return $items;
    }
}
