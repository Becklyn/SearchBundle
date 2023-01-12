<?php

namespace Becklyn\SearchBundle\Entity;


/**
 * Describes searchable entities, that are localized
 */
interface LocalizedSearchableEntityInterface extends SearchableEntityInterface
{
    /**
     * Returns the language of the entity
     *
     * @return LanguageInterface
     */
    public function getLanguage ();
}
