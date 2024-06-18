<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Service\Comparator;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute\BooleanComparator;

class PcmtBooleanComparator extends BooleanComparator
{
    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        $default = [
            'locale' => null,
            'scope'  => null,
            'data'   => null,
        ];
        $originals = array_merge($default, $originals);

        $isNull = null === $originals['data'] && null === $data['data'];

        $isEquals = $originals['data'] === (bool) $data['data'];

        if ($isNull || $isEquals) {
            return null;
        }

        return $data;
    }
}
