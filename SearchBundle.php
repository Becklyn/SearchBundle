<?php

namespace SearchBundle;

use Becklyn\SearchBundle\DependencyInjection\SearchBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class SearchBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    protected function getContainerExtensionClass ()
    {
        return SearchBundleExtension::class;
    }
}
