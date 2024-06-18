<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class DatagridConfigurationBuilder
{
    /** @var DatagridConfiguration */
    private $datagridConfiguration;

    public function __construct()
    {
        $this->datagridConfiguration = DatagridConfiguration::create([]);
    }

    public function build(): DatagridConfiguration
    {
        return $this->datagridConfiguration;
    }
}