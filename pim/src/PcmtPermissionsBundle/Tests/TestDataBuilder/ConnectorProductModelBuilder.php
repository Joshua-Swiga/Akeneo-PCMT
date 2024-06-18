<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;

class ConnectorProductModelBuilder
{
    /** @var ConnectorProductModel */
    private $productModel;

    public function __construct()
    {
        $this->productModel = new ConnectorProductModel(
            1,
            'identifier',
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            null,
            'familyCode',
            '',
            [],
            [],
            [],
            new ReadValueCollection()
        );
    }

    public function build(): ConnectorProductModel
    {
        return $this->productModel;
    }
}