<?php

namespace Pdir\SocialFeedBundle\DependencyInjection;

use Pdir\SocialFeedBundle\Importer\InstagramRequestCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class PdirSocialFeedExtension extends ConfigurableExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yml');

        $container->getDefinition(InstagramRequestCache::class)->setArgument(1, (int) $mergedConfig['cache_ttl']);
    }
}
