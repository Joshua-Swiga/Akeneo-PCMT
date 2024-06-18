<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\ArrayConverter\FlatToStandard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\MetricConverter as BaseMetricConverter;

class MetricConverter extends BaseMetricConverter
{
    /**
     * {@inheritdoc}
     *
     * Sometimes the metric unit may contain spaces, like 'GRAMS PER CUBIC CENTIMETRE'
     * In such cases original code did not work properly and cut the metric unit to first word.
     * The following code aims to fix that.
     */
    public function convert(array $attributeFieldInfo, $value)
    {
        $data = parent::convert($attributeFieldInfo, $value);
        foreach ($data as $key => $values) {
            $d = $values[0]['data'];
            if ($d) {
                $valueRecreated = $d['amount'] . ' ' . $d['unit'];
                if ($valueRecreated !== $value) {
                    $data[$key][0]['data']['unit'] = mb_substr($value, mb_strpos($value, ' ') + 1);
                }
            }
        }

        return $data;
    }
}