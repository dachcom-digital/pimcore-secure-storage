<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
                            ->booleanNode('allow_asset_preview_image_generation')->defaultFalse()->end()
                            ->booleanNode('allow_asset_update_preview_image_generation')->defaultFalse()->end()
                            ->booleanNode('allow_image_optimizing')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('pimcore_asset_protection')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('htaccess_protection_public_directories')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('paths')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('omit_backend_search_indexing')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('paths')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

            ->end();

        return $treeBuilder;
    }
}
