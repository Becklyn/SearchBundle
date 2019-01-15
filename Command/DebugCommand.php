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

        $sections = [
            "Indices" => $this->client->cat()->indices(),
            "Allocation" => $this->client->cat()->allocation(),
            "Health" => $this->client->cat()->health(),
            "Master" => $this->client->cat()->master(),
            "Nodes" => $this->client->cat()->nodes(),
        ];

        foreach ($sections as $title => $data)
        {
            $io->section($title);

            if (empty($data))
            {
                $io->comment("No data.");
                continue;
            }

            $headers = \array_keys($data[0]);
            $io->table($headers, $data);
        }
    }
}
