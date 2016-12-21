<?php

namespace Becklyn\SearchBundle\LanguageIntegration\AnnotatedField;

use Becklyn\SearchBundle\Mapping\Field;

/**
 * A wrapper class for an annotated method
 */
class AnnotatedMethod
{
    /**
     * @var \ReflectionProperty
     */
    private $method;


    /**
     * @var Field
     */
    private $annotation;



    /**
     * @param \ReflectionMethod $method
     * @param Field               $annotation
     */
    public function __construct (\ReflectionMethod $method, Field $annotation)
    {
        $this->method = $method;
        $this->annotation = $annotation;
    }



    /**
     * @return \ReflectionMethod
     */
    public function getMethod () : \ReflectionMethod
    {
        return $this->method;
    }



    /**
     * @return Field
     */
    public function getAnnotation () : Field
    {
        return $this->annotation;
    }
}
