<?php

namespace Becklyn\SearchBundle\Entity;

use Becklyn\Interfaces\LanguageInterface;


/**
 * Describes searchable entities, that are translated
 */
interface TranslatedSearchableEntityInterface extends SearchableEntityInterface
{
    /**
     * Returns the language of the entity
     *
     * @return LanguageInterface
     */
    public function getLanguage ();
}
