<?php

namespace Becklyn\SearchBundle\DependencyInjection;

use Becklyn\SearchBundle\SearchBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


class SearchBundleExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new SearchBundleConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        // load main services.yml
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // set config for elasticsearch client
        $container->getDefinition("becklyn.search.elasticsearch")
            ->replaceArgument(0, $config["server"])
            ->replaceArgument(1, $config["index"]);

        // set config for metadata analysis
        $container->getDefinition("becklyn.search.index.configuration.analysis")
            ->replaceArgument(0, $config["filters"])
            ->replaceArgument(1, $config["analyzers"]);

        // set config for elasticsearch client
        $container->getDefinition("becklyn.search.index.configuration.language")
            ->replaceArgument(0, $config["index"])
            ->replaceArgument(1, $config["localized"])
            ->replaceArgument(2, $config["unlocalized"] ?? []);
    }



    /**
     * @inheritdoc
     */
    public function getAlias ()
    {
        return SearchBundle::BUNDLE_ALIAS;
    }
}
