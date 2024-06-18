<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pcmt_core');

        $rootNode
            ->children()
            ->integerNode('defaultSessionTimeMins')->defaultValue(20)->end()
            ->end()
            ->children()
            ->arrayNode('data_download')
            ->children()
            ->scalarNode('dirPath')->end() //implement default value
            ->end();

        return $treeBuilder;
    }
}