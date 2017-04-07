<?php

namespace Becklyn\SearchBundle\Accessor;

use Becklyn\SearchBundle\Entity\SearchableEntityInterface;
use Becklyn\SearchBundle\FormatProcessor\TextFormatProcessor;
use Becklyn\SearchBundle\Metadata\SearchItem\SearchItemContentInterface;
use Becklyn\SearchBundle\Metadata\SearchItemField;
use Becklyn\SearchText\SearchTextTransformer;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;


/**
 * Accesses values in the given entities
 */
class EntityValueAccessor
{
    /**
     * @var PropertyAccessor
     */
    private $accessor;


    /**
     * @var array
     */
    private $formatProcessors = [
        "html" => [
            "service" => "becklyn.search.format_processor.no_op",
            "html_post_process" => true,
        ],
    ];


    /**
     * @var SearchTextTransformer
     */
    private $htmlTransformer;


    /**
     * @var ContainerInterface
     */
    private $container;



    /**
     * @param ContainerInterface $container
     * @param array              $formatProcessors
     */
    public function __construct (ContainerInterface $container, array $formatProcessors = [])
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->container = $container;
        $this->formatProcessors = array_replace($this->formatProcessors, $formatProcessors);
        $this->htmlTransformer = new SearchTextTransformer();
    }



    /**
     * Returns the value of the field
     *
     * @param SearchableEntityInterface $entity
     * @param SearchItemField           $field
     *
     * @return string
     */
    public function getValue (SearchableEntityInterface $entity, SearchItemField $field) : string
    {
        $value = (string) $this->getRawValue($entity, $field);

        return $this->processValue($field, $value);
    }



    /**
     * Returns the raw value of the field
     *
     * @param SearchableEntityInterface  $entity
     * @param SearchItemContentInterface $content
     *
     * @return string|null
     */
    public function getRawValue (SearchableEntityInterface $entity, SearchItemContentInterface $content)
    {
        $value = $this->accessor->getValue($entity, $content->getAccessorName());

        return !empty($value)
            ? (string) $value
            : null;
    }



    /**
     * Processes HTML before it is indexed
     *
     * @param SearchItemField $field
     * @param string          $text
     *
     * @return string
     */
    private function processValue (SearchItemField $field, string $text) : string
    {
        $processorDefinition = $this->formatProcessors[$field->getFormat()] ?? null;

        if (null === $processorDefinition)
        {
            return $text;
        }

        try
        {
            $processor = $this->container->get($processorDefinition["service"], ContainerInterface::NULL_ON_INVALID_REFERENCE);

            if (!$processor instanceof TextFormatProcessor)
            {
                throw new InvalidConfigurationException(sprintf(
                    "Processor for format '%s' was found, but the service '%s' doesn't implement the required interface '%s'.",
                    $field->getFormat(),
                    $processorDefinition["service"],
                    TextFormatProcessor::class
                ));
            }

            // apply processor
            $text = $processor->process($text);

            //
            if ($processorDefinition["html_post_process"])
            {
                $text = $this->htmlTransformer->transform($text);
            }

            return $text;
        }
        catch (ServiceNotFoundException $e)
        {
            throw new InvalidConfigurationException(sprintf(
                "Can't use processor for format '%s' as the service '%s' could not be found.",
                $field->getFormat(),
                $processorDefinition["service"]
            ), $e);
        }
    }
}
