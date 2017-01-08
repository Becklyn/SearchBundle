<?php

namespace Becklyn\SearchBundle\Command;

use Becklyn\SearchBundle\Elasticsearch\ElasticsearchClient;
use Elasticsearch\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class DebugCommand extends ContainerAwareCommand
{
    /**
     * @var Client
     */
    private $client;



    /**
     * @inheritDoc
     */
    public function __construct (ElasticsearchClient $client)
    {
        parent::__construct("becklyn:search:debug");
        $this->client = $client->getClient();
    }



    /**
     * @inheritDoc
     */
    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("Search debug");

        $io->write("> Testing the connection to elasticsearch: ");

        if ($this->client->ping())
        {
            $io->write("<fg=green>ok</>");
        }
        else
        {
            $io->write("<fg=red>not found</>");
        }

        $io->section("Indices");
        $io->block($this->client->cat()->indices());

        $io->section("Allocation");
        $io->block($this->client->cat()->allocation());

        $io->section("Health");
        $io->block($this->client->cat()->health());

        $io->section("Master");
        $io->block($this->client->cat()->master());

        $io->section("Nodes");
        $io->block($this->client->cat()->nodes());
    }
}
