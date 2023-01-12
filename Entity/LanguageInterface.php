<?php

namespace Becklyn\SearchBundle\Entity;

/**
 * A common interface for defining a language
 */
interface LanguageInterface
{
    /**
     * Returns the name of the language
     *
     * @return string
     */
    public function getName () : string;



    /**
     * Returns the ISO short code of the language
     *
     * @return string
     */
    public function getCode () : string;
}
