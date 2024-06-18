<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\DataCollector;

use Akeneo\Platform\VersionProviderInterface;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;

class PcmtVersionDataCollector implements DataCollectorInterface
{
    /** @var VersionProviderInterface */
    protected $versionProvider;

    public function __construct(VersionProviderInterface $versionProvider)
    {
        $this->versionProvider = $versionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return [
            'pcmt_version' => $this->versionProvider->getFullVersion(),
        ];
    }
}