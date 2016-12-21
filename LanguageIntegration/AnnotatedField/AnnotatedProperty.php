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
     * @var Field
     */
    private $annotation;



    /**
     * @param \ReflectionProperty $property
     * @param Field               $annotation
     */
    public function __construct (\ReflectionProperty $property, Field $annotation)
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
     * @return Field
     */
    public function getAnnotation () : Field
    {
        return $this->annotation;
    }
}
