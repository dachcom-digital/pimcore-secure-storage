<?php

namespace SecureStorageBundle\DependencyInjection;

use SecureStorageBundle\Encrypter\OpenSslEncrypter;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('secure_storage');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('encrypter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue(OpenSslEncrypter::class)->end()
                        ->variableNode('options')->defaultValue([])->end()
                    ->end()
                ->end()
                ->arrayNode('secured_fly_system_storages')
                    ->prototype('array')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('storage')
                            ->isRequired()
                            ->validate()
                                ->ifNull()
                                ->thenInvalid('Invalid storage %s')
                            ->end()
                        ->end()
                        ->arrayNode('paths')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
