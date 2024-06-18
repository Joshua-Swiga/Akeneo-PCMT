<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;

class ConnectorProductModelListBuilder
{
    /** @var ConnectorProductModelList */
    private $list;

    public function __construct()
    {
        $this->list = new ConnectorProductModelList(
            10,
            []
        );
    }

    public function build(): ConnectorProductModelList
    {
        return $this->list;
    }
}