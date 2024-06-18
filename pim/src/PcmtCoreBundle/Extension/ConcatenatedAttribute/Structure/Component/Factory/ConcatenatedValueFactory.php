<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\AbstractValueFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Used for generating value for this attribute in connection to product/product model
 */
class ConcatenatedValueFactory extends AbstractValueFactory
{
    /**
     * {@inheritdoc}
     */
    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData): ?string
    {
        /*
         * here now data is in format of array wth single value :
         * array:1 [
        *     0 => "594877:13.00 EUR"
        * ]
         * possibly it can be array with elements: [ 0 => attribute1, 1 => separator1, 2 => attribute 2]
         */
        if (is_array($data)) {
            return $data[0];
        }

        return $data;
    }
}