<?php

namespace Becklyn\SearchBundle\LanguageIntegration\AnnotatedField;

use Becklyn\SearchBundle\Mapping\Field;


/**
 * A wrapper class for an annotated property
 */
class AnnotatedProperty
{
    /**
     * @var \ReflectionProperty
     */
    private $property;


    /**
     * @var object
     */
    private $annotation;



    /**
     * @param \ReflectionProperty $property
     * @param object              $annotation
     */
    public function __construct (\ReflectionProperty $property, $annotation)
    {
        $this->property = $property;
        $this->annotation = $annotation;
    }



    /**
     * @return \ReflectionProperty
     */
    public function getProperty () : \ReflectionProperty
    {
        return $this->property;
    }



    /**
     * @return object
     */
    public function getAnnotation ()
    {
        return $this->annotation;
    }
}
