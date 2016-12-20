<?php

namespace Becklyn\SearchBundle\Index\Configuration;

use Becklyn\SearchBundle\Exception\InvalidSearchConfigurationException;


/**
 * Custom configuration of all filters / analyzers
 */
class AnalysisConfiguration
{
    /**
     * The list of defined filters
     *
     * @var array
     */
    private $filters = [
        "default.filter.de" => [
            "type" => "stemmer",
            "name" => "light_german",
        ],
        "default.filter.shingle" => [
            "min_shingle_size" => 2,
            "max_shingle_size" => 5,
            "type" => "shingle",
        ],
    ];

    private $analyzers = [
        "default.analyzer.de" => [
            "tokenizer" => "lowercase",
            "filter" => [
                "standard",
                "lowercase",
                "default.filter.de",
                "asciifolding",
                "default.filter.shingle",
            ],
            "type" => "custom",
        ],
    ];



    /**
     * @param array $additionalFilters
     * @param array $additionalAnalyzers
     */
    public function __construct (array $additionalFilters = [], array $additionalAnalyzers = [])
    {
        foreach ($additionalFilters as $filterName => $filterConfiguration)
        {
            $this->registerFilter($filterName, $filterConfiguration);
        }

        foreach ($additionalAnalyzers as $analyzerName => $analyzerConfiguration)
        {
            $this->registerAnalyzer($analyzerName, $analyzerConfiguration);
        }
    }



    /**
     * Registers a new filter
     *
     * @param string $name
     * @param array  $configuration
     *
     * @throws InvalidSearchConfigurationException
     */
    public function registerFilter (string $name, array $configuration)
    {
        if (array_key_exists($name, $this->filters))
        {
            throw new InvalidSearchConfigurationException(sprintf(
                "There already is a filter with name '%s' registered.",
                $name
            ));
        }

        $this->filters[$name] = $configuration;
    }



    /**
     * Register a new analyzer
     *
     * @param string $name
     * @param array  $configuration
     *
     * @throws InvalidSearchConfigurationException
     */
    public function registerAnalyzer (string $name, array $configuration)
    {
        if (array_key_exists($name, $this->analyzers))
        {
            throw new InvalidSearchConfigurationException(sprintf(
                "There already is an analyzer with name '%s' registered.",
                $name
            ));
        }

        $this->analyzers[$name] = $configuration;
    }



    /**
     * Returns a list of all analyzers
     *
     * @param string $name
     *
     * @return array
     * @throws InvalidSearchConfigurationException
     */
    public function getAnalyzer (string $name)
    {
        if (!isset($this->analyzers[$name]))
        {
            throw new InvalidSearchConfigurationException(sprintf(
                "No analyzer registered with name '%s'.",
                $name
            ));
        }

        return $this->analyzers[$name];
    }



    /**
     * Returns a list of all filters
     *
     * @param string $name
     *
     * @return array
     * @throws InvalidSearchConfigurationException
     */
    public function getFilter (string $name)
    {
        if (!isset($this->analyzers[$name]))
        {
            throw new InvalidSearchConfigurationException(sprintf(
                "No filter registered with name '%s'.",
                $name
            ));
        }

        return $this->filters[$name];
    }
}
