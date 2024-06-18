<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;

class ConnectorProductBuilder
{
    /** @var ConnectorProduct */
    private $product;

    public function __construct()
    {
        $this->product = new ConnectorProduct(
            1,
            'identifier',
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            true,
            'familyCode',
            [],
            [],
            'parentProductModelCode',
            [],
            [],
            new ReadValueCollection()
        );
    }

    public function build(): ConnectorProduct
    {
        return $this->product;
    }
}