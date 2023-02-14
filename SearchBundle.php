<?php

namespace Becklyn\SearchBundle;

use Becklyn\SearchBundle\DependencyInjection\SearchBundleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class SearchBundle extends Bundle
{
    public const BUNDLE_ALIAS = "becklyn_search";

    /**
     * @inheritdoc
     */
    public function getContainerExtension () : ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new SearchBundleExtension();
        }

        return $this->extension;
    }
}
