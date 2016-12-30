<?php

namespace Becklyn\SearchBundle\Command;

use Becklyn\Interfaces\LanguageInterface;
use Becklyn\SearchBundle\Search\Result\EntitySearchHits;
use Becklyn\SearchBundle\Search\Result\SearchHit;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class SearchCommand extends ContainerAwareCommand
{
    const APP_DEFAULT_LANGUAGE_CLASS = "AppBundle\\Entity\\Language";


    /**
     * @inheritdoc
     */
    public function __construct ()
    {
        parent::__construct("becklyn:search:client");
    }



    /**
     * @inheritdoc
     */
    protected function configure ()
    {
        parent::configure();

        $this
            ->setDescription("CLI client for the search bundle")
            ->addArgument("query", InputArgument::REQUIRED)
            ->addOption("language", null, InputOption::VALUE_OPTIONAL, "The language code to search with")
            ->addOption("entity", null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, "The entity classes that should be searched. If no entities are given, all entities are searched.", [])
            ->addOption("languageEntity", null, InputOption::VALUE_OPTIONAL, "The FQCN of the language entity", self::APP_DEFAULT_LANGUAGE_CLASS);
    }



    /**
     * @inheritdoc
     */
    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("CLI search client");

        $query = $input->getArgument("query");
        $language = $input->getOption("language");
        $itemClasses = $input->getOption("entity");
        $languageEntity = $input->getOption("languageEntity");

        if (null !== $language)
        {
            if (!class_exists($languageEntity))
            {
                $io->error(sprintf(
                    "The command was not able to load the language class in the namespace: '%s'.",
                    $languageEntity
                ));
                return;
            }

            $class = new \ReflectionClass($languageEntity);
            if (!$class->implementsInterface(LanguageInterface::class))
            {
                $io->error(sprintf(
                    "The language entity class '%s' does not implement the LanguageInterface.",
                    $languageEntity
                ));
                return;
            }

            $language = $this->getContainer()->get("doctrine")
                ->getRepository($languageEntity)
                ->findOneBy([
                    "code" => $language,
                ]);
        }

        $searchClient = $this->getContainer()->get("becklyn.search.client");

        $result = $searchClient->search($query, $language, $itemClasses);

        /** @var EntitySearchHits $entityResults */
        foreach ($result as $entityResults)
        {
            $counter = 0;
            $io->section(sprintf("Search results for: %s", $entityResults->getEntityClass()));

            $io->table(
                ["#", "ID", "Score", "Highlights"],
                    array_map(
                    function (SearchHit $hit) use (&$counter)
                    {
                        $counter += 1;
                        $highlights = "-> " . implode("\n-> ", $hit->getAllHighlights());
                        $highlights = str_replace(["<mark>", "</mark>"], ["<fg=magenta>", "</fg=magenta>"], $highlights);

                        return [
                            $counter,
                            '<fg=yellow>' . $hit->getEntity()->getId() . '</>',
                            $hit->getScore(),
                            $highlights,
                        ];
                    },
                    $entityResults->getHits()
                )
            );
        }

        if (0 === count($result))
        {
            $io->block("No hits found for query „{$query}“.");
        }
    }
}
