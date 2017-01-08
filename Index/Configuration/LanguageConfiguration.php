<?php

namespace Becklyn\SearchBundle\Index\Configuration;

use Becklyn\SearchBundle\Entity\LocalizedSearchableEntityInterface;
use Becklyn\SearchBundle\Entity\SearchableEntityInterface;
use Becklyn\SearchBundle\Exception\InvalidSearchConfigurationException;


/**
 * Configuration, which languages uses which filters
 */
class LanguageConfiguration
{
    const INDEX_LANGUAGE_PLACEHOLDER = "{language}";
    const UNLOCALIZED_LANGUAGE_CODE = "unlocalized";

    /**
     * @var string
     */
    private $indexPattern;


    /**
     * @var array
     */
    private $languageData;


    /**
     * @var array
     */
    private $unlocalizedData = [
        "analyzer" => "default_analyzer_de",
        "search_analyzer" => "default_analyzer_de",
    ];


    /**
     * @param string $indexPattern
     * @param array  $languageData
     */
    public function __construct (string $indexPattern, array $languageData, array $unlocalizedData)
    {
        $this->indexPattern = $indexPattern;
        $this->languageData = $languageData;

        if (!empty($unlocalizedData))
        {
            $this->unlocalizedData = $unlocalizedData;
        }
    }



    /**
     * Returns the index name
     *
     * @param string|null $language
     *
     * @return string
     */
    public function getIndexName (string $language = null) : string
    {
        if (null === $language)
        {
            $language = self::UNLOCALIZED_LANGUAGE_CODE;
        }

        return str_replace(self::INDEX_LANGUAGE_PLACEHOLDER, $language, $this->indexPattern);
    }



    /**
     * Returns the analyzer for the given language
     *
     * @param string|null $language
     *
     * @return string
     * @throws InvalidSearchConfigurationException
     */
    public function getIndexAnalyzer (string $language = null) : string
    {
        return $this->fetchLanguageConfiguration($language)["analyzer"]["index"];
    }



    /**
     * Returns the search analyzer for the given language
     *
     * @param string|null $language
     *
     * @return string
     * @throws InvalidSearchConfigurationException
     */
    public function getSearchAnalyzer (string $language = null) : string
    {
        return $this->fetchLanguageConfiguration($language)["analyzer"]["search"];
    }



    /**
     * Fetches a configuration value
     *
     * @param string|null $language
     *
     * @return array
     * @throws InvalidSearchConfigurationException
     */
    private function fetchLanguageConfiguration (string $language = null) : array
    {
        if (null === $language)
        {
            $data = $this->unlocalizedData;
        }
        else
        {
            if (!array_key_exists($language, $this->languageData))
            {
                throw new InvalidSearchConfigurationException(sprintf(
                    "There is no language configuration available for language '%s'. It needs to be added to the search configuration in config.yml",
                    $language
                ));
            }

            $data = $this->languageData[$language];
        }

        return $data;
    }



    /**
     * Returns a list of all languages
     *
     * @return array
     */
    public function getAllLanguages () : array
    {
        $languages = array_keys($this->languageData);
        array_unshift($languages, null);

        return $languages;
    }



    /**
     * Returns the index for the given entity
     *
     * @param SearchableEntityInterface $entity
     *
     * @return string
     */
    public function getIndexForEntity (SearchableEntityInterface $entity) : string
    {
        if ($entity instanceof LocalizedSearchableEntityInterface)
        {
            return $this->getIndexName($entity->getLanguage()->getCode());
        }

        return $this->getIndexName(null);
    }
}
