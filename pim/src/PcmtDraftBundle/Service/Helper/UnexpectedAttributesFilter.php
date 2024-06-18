<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Helper;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class UnexpectedAttributesFilter
{
    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributeProvider;

    public function __construct(EntityWithFamilyVariantAttributesProvider $attributeProvider)
    {
        $this->attributeProvider = $attributeProvider;
    }

    public function filter(EntityWithFamilyVariantInterface $entityWithFamilyVariant, array $values): array
    {
        $attributes = $this->attributeProvider->getAttributes($entityWithFamilyVariant);
        $levelAttributeCodes = array_map(
            function (AttributeInterface $attribute) {
                return $attribute->getCode();
            },
            $attributes
        );

        return array_filter($values, function ($key) use ($levelAttributeCodes) {
            return in_array($key, $levelAttributeCodes);
        }, ARRAY_FILTER_USE_KEY);
    }
}