<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PcmtCustomDatasetExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('updaters.yml');
        $loader->load('array_converters.yml');
        $loader->load('listeners.yml');
        $loader->load('normalizers.yml');
        $loader->load('readers.yml');
        $loader->load('processors.yml');
        $loader->load('writers.yml');
        $loader->load('steps.yml');
        $loader->load('jobs.yml');
        $loader->load('forms.yml');
        $loader->load('providers.yml');
        $loader->load('repositories.yml');
    }

    public function getAlias(): string
    {
        return 'pcmt_custom_dataset';
    }
}