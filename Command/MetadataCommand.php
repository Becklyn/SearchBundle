<?php

namespace Becklyn\SearchBundle\Command;

use Becklyn\SearchBundle\Metadata\SearchItem;
use Becklyn\SearchBundle\Metadata\SearchItemField;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class MetadataCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    public function __construct ()
    {
        parent::__construct("becklyn:search:metadata");
    }



    /**
     * @inheritDoc
     */
    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Search Metadata");

        foreach ($this->loadItems() as $item)
        {
            $io->section($item->getFqcn());

            if ($item->isLocalized())
            {
                $io->note('<fg=blue>localized</>');
            }


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
        }

        $io->success("Done.");
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
