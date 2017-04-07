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
     * @var object
     */
    private $annotation;



    /**
     * @param \ReflectionMethod $method
     * @param object            $annotation
     */
    public function __construct (\ReflectionMethod $method, $annotation)
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
     * @return object
     */
    public function getAnnotation ()
    {
        return $this->annotation;
    }
}
