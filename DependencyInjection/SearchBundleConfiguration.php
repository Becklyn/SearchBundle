<?php

namespace Becklyn\SearchBundle\DependencyInjection;

use Becklyn\SearchBundle\SearchBundle;
use SearchBundle\Metadata\Configuration\LanguageConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class SearchBundleConfiguration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder ()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(SearchBundle::BUNDLE_ALIAS);

        $rootNode
            ->children()
                ->scalarNode("server")
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode("index")
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                    ->ifTrue(function ($value) {
                            return 1 !== substr_count($value, LanguageConfiguration::INDEX_LANGUAGE_PLACEHOLDER);
                        })
                        ->thenInvalid(sprintf(
                            "The index must use exactly one language placeholder '%s'.",
                            LanguageConfiguration::INDEX_LANGUAGE_PLACEHOLDER
                        ))
                    ->end()
                ->end()
                ->arrayNode("filters")
                    ->useAttributeAsKey("name")
                    ->prototype("array")
                        ->children()
                            ->scalarNode("type")
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                        ->ignoreExtraKeys(false)
                    ->end()
                    ->defaultValue([])
                ->end()
                ->arrayNode("analyzers")
                    ->useAttributeAsKey("name")
                    ->prototype("array")
                        ->children()
                            ->scalarNode("tokenizer")
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->arrayNode("char_filter")
                                ->prototype("scalar")->end()
                            ->end()
                            ->arrayNode("filter")
                                ->prototype("scalar")->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("languages")
                    ->useAttributeAsKey("code")
                    ->prototype("array")
                        ->children()
                            ->scalarNode("analyzer")
                            ->end()
                        ->end()
                    ->end()
                    ->defaultValue([])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
