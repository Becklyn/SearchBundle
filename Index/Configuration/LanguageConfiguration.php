<?php

namespace Becklyn\SearchBundle\Metadata\Configuration;

use Becklyn\SearchBundle\Exception\InvalidSearchConfigurationException;


/**
 * Configuration, which languages uses which filters
 */
class LanguageConfiguration
{
    const INDEX_LANGUAGE_PLACEHOLDER = "{language}";

    /**
     * @var string
     */
    private $indexPattern;


    /**
     * @var array
     */
    private $languageData;



    /**
     * @param string $indexPattern
     * @param array  $languageData
     */
    public function __construct (string $indexPattern, array $languageData)
    {
        $this->indexPattern = $indexPattern;
        $this->languageData = $languageData;
    }



    /**
     * Returns the index name
     *
     * @param string $language
     *
     * @return string
     */
    public function getIndexName (string $language) : string
    {
        return str_replace(self::INDEX_LANGUAGE_PLACEHOLDER, $language, $this->indexPattern);
    }



    /**
     * Returns the analyzer for the given language
     *
     * @param string $language
     *
     * @return string
     * @throws InvalidSearchConfigurationException
     */
    public function getAnalyzer (string $language) : string
    {
        if (!array_key_exists($language, $this->languageData))
        {
            throw new InvalidSearchConfigurationException(sprintf(
                "There is no language configuration available for language '%s'. It needs to be added to the search configuration in config.yml",
                $language
            ));
        }

        return $this->languageData[$language]["analyzer"];
    }
}
