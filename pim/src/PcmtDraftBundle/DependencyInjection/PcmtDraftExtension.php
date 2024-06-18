<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PcmtDraftExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        //load and merge configuration
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('normalizers.yml');
        $loader->load('providers.yml');
        $loader->load('savers.yml');
        $loader->load('services.yml');
        $loader->load('updaters.yml');
        $loader->load('widget.yml');
        $loader->load('datagrid_listeners.yml');
        $loader->load('pagers.yml');
        $loader->load('data_sources.yml');
        $loader->load('steps.yml');
        $loader->load('jobs.yml');
        $loader->load('writers.yml');
        $loader->load('tasklets.yml');
        $loader->load('repositories.yml');
        $loader->load('fixtures.yml');
    }

    public function getAlias(): string
    {
        return 'pcmt_draft';
    }
}