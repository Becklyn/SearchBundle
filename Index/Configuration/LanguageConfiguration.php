<?php

namespace Becklyn\SearchBundle\Index\Configuration;

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
    public function getAnalyzer (string $language = null) : string
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

        return $data["analyzer"];
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
}
