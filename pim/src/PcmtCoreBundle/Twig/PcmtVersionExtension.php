<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Twig;

use Akeneo\Platform\VersionProviderInterface;

class PcmtVersionExtension extends \Twig_Extension
{
    /** @var VersionProviderInterface */
    private $versionProvider;

    public function __construct(VersionProviderInterface $versionProvider)
    {
        $this->versionProvider = $versionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('pcmt_version', [$this, 'version']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function version()
    {
        return $this->versionProvider->getFullVersion();
    }
}