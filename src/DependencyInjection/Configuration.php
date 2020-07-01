<?php

namespace Pdir\SocialFeedBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('pdir_socialfeed');

        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // Backwards compatibility
            $rootNode = $treeBuilder->root('pdir_socialfeed');
        }

        $rootNode
            ->children()
            ->integerNode('cache_ttl')->defaultValue(3600)->end()
            ->end();

        return $treeBuilder;
    }
}
