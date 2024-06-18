<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Tests\ArrayConverter\StandardToFlat;

use PcmtCustomDatasetBundle\ArrayConverter\StandardToFlat\DatagridView;

class FakeDatagridView extends DatagridView
{
    /**
     * {@inheritdoc}
     */
    public function convertProperty($property, $data, array $convertedItem, array $options): array
    {
        return parent::convertProperty($property, $data, $convertedItem, $options);
    }
}