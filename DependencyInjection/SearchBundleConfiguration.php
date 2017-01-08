<?php

namespace Becklyn\SearchBundle\DependencyInjection;

use Becklyn\SearchBundle\Index\Configuration\LanguageConfiguration;
use Becklyn\SearchBundle\SearchBundle;
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
                ->arrayNode("format_processors")
                    ->prototype("array")
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($stringValue) { return ["service" => $stringValue]; })
                        ->end()
                        ->children()
                            ->scalarNode("service")
                                ->isRequired()
                            ->end()
                            ->scalarNode("html_post_process")
                                ->defaultFalse()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("filters")
                    ->prototype("array")
                        ->children()
                            ->scalarNode("type")
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                        ->ignoreExtraKeys(false)
                    ->end()
                ->end()
                ->arrayNode("analyzers")
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
                ->arrayNode("localized")
                    ->prototype("array")
                        ->children()
                            ->scalarNode("analyzer")->end()
                            ->scalarNode("search_analyzer")->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("unlocalized")
                    ->children()
                        ->scalarNode("analyzer")->end()
                        ->scalarNode("search_analyzer")->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
